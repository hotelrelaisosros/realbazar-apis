<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use Illuminate\Support\Facades\Log;
use App\Models\CartItem;
use App\Jobs\RemoveCartItem;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\BespokeCustomizationType;
use App\Models\GemStone;
use App\Models\BirthStone;





class CartController extends Controller
{
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'http')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }
    public function addToCart(Request $request)
    {
        $valid = Validator::make($request->all(), [

            'product_id' => 'required|numeric|exists:products,id',
            'product_image_id' => 'nullable|numeric|exists:product_images,id',
            'variation_id' => 'nullable|numeric|exists:product_variations,id',
            'bespoke_type' => 'nullable|numeric|exists:bespoke_customization_types,id',
            'birth_stone' => 'nullable|numeric|exists:birth_stones,id',
            'gem_stone' => 'nullable|numeric|exists:gem_stones,id',

        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'Message' => 'Validation errors',
                'errors' => $valid->errors(),
                'request_data' => $request->all()
            ]);
        }


        $validated = $valid->validated();

        $product = Product::find($validated["product_id"]);

        $variation = null;
        $productImage = null;
        $price_counter = $product->price - $product->discount_price;
        if (!empty($validated["product_image_id"]) && empty($validated["variation_id"])) {
            $productImage = ProductImage::find($validated["product_image_id"]);
            if ($productImage) {
                $productImage->image = $this->formatImageUrl($productImage->image);
            }
        } elseif (!empty($validated["product_image_id"]) && !empty($validated["variation_id"])) {
            $variation = ProductVariation::find($validated["variation_id"]);
            if ($variation) {
                $productImage = ProductImage::where('id', $validated["product_image_id"])
                    ->where('variant_id', $variation->id)
                    ->first();
            }
            $price_counter +=  $variation->price ?? 0;
        }


        if (!empty($validated["bespoke_type"])) {
            $bsp_type = BespokeCustomizationType::find($validated["bespoke_type"]);
            $price_counter +=  $bsp_type->price ?? 0;
        }
        if (!empty($request["birth_stone"])) {
            $birth_stone = BirthStone::find($request["birth_stone"]);
            $price_counter +=  $birth_stone->price ?? 0;
        }
        if (!empty($request["gem_stone"])) {
            $gem_stone = GemStone::find($request["gem_stone"]);
            $price_counter +=  $gem_stone->price ?? 0;
        }

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $user = auth()->user()->id;
        // Check if the product is a ring
        $isRing =  Product::isRing($validated['product_id'])->exists();

        $customizables = [];

        if ($isRing) {
            $customizables = $this->getCustomizablesFromRequest($request);
        }

        try {

            $cartItemId = $isRing ? $product->id . '-' . strtoupper(substr(uniqid(), -6)) : $product->id;

            // Create the cart object
            if ($isRing) {
                $models =  [
                    'product' => $product,
                    'product_image' => $productImage,
                    'variation' => $variation,
                    'bespoke_type' => $bsp_type ?? null,
                    'birth_stone' => $birth_stone ?? null,
                    'gem_stone' => $gem_stone ?? null,
                ];
            } else {
                $models = [
                    'product' => $product,
                    'product_image' => $productImage,
                    'variation' => $variation,
                ];
            }
            $cartObj = [
                'id' => $cartItemId, // Use unique id for rings, just product id for non-rings
                'name' => $product->title,
                'price' => $price_counter,
                'quantity' => 1,
                'attributes' => $customizables, // Only rings will have attributes
                'associatedModel' => $models,
            ];

            // Check if the product is already in the cart
            Cart::session($user)->getContent();

            // Adding item to cart
            Cart::session($user)->add($cartObj);
            // Log::info('Cart in session:', ['cart_items' => Cart::session($user)->getContent()]);

            // Create a cart item record in the database
            // $cartItem = CartItem::create([
            //     'user_id' => $user,
            //     'product_id' => $product->id,
            //     'name' => $product->title,
            //     'price' => $product->price,
            //     'quantity' => 1,
            //     'attributes' =>  json_encode($customizables) ?? null,
            // ]);

            // // Dispatch remove cart item after 48 hours (optional)
            // RemoveCartItem::dispatch($user, $cartItem->id)->delay(now()->addHours(48));

            return response()->json(['status' => true, 'Message' => 'Product added to cart'], 202);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'Message' => $e->getMessage()], 500);
        }
    }

    public function updateCartCustomization(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'cart_id' => 'required|string',
            'fields_to_nullify' => 'required|array',
            'fields_to_nullify.*' => 'in:bespoke_type,birth_stone,gem_stone',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validated->errors()
            ], 422);
        }

        if (!str_contains($request->input("cart_id"), "-")) {
            return response()->json(['success' => false, 'message' => 'Cart customization cannot be updated'], 404);
        }

        $userID = auth()->user()->id;
        $cartId = $request->input('cart_id');
        $fieldsToNullify = $request->input('fields_to_nullify');

        // Retrieve the cart item
        $cartItem = Cart::session($userID)->get($cartId);

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $updatedAttributes = $cartItem->attributes;
        $associatedModel = $cartItem->associatedModel;

        // $updatedTotal = $cartItem->total;

        foreach ($fieldsToNullify as $field) {
            // Nullify attributes if they exist

            if ($updatedAttributes && $updatedAttributes->has($field)) {
                $updatedAttributes[$field] = null;
            }
            // Handle associated models
            if ($associatedModel && array_key_exists($field, $associatedModel)) {
                if (isset($associatedModel[$field]['price'])) {
                    // Optionally adjust the total price if needed
                    $updatedTotal -= $associatedModel[$field]['price'];
                }
                unset($associatedModel[$field]); // Remove the field from the associated model
            }
        }

        // Safeguard against negative totals
        // $updatedTotal = max(0, $updatedTotal);

        // Update the cart item
        Cart::session($userID)->update($cartId, [
            'attributes' => $updatedAttributes,
            'price' => $cartItem->price,
            'quantity' => $cartItem->quantity,
            'associatedModel' => $associatedModel, // Update the associated model

            // 'total' => $updatedTotal,
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Cart customization updated successfully!',
            'cart_item' => $cartItem,
            // 'updated_total' => $updatedTotal
        ]);
    }


    private function getCustomizablesFromRequest(Request $request): array
    {
        return [
            'metal_type' => $request->metal_type ?? null,
            'gem_shape_id' => $request->gem_shape_id ?? null,
            'band_width_id' => $request->band_width_id ?? null,
            'accent_stone_type_id' => $request->accent_stone_type_id ?? null,
            'setting_height_id' => $request->setting_height_id ?? null,
            'prong_style_id' => $request->prong_style_id ?? null,
            'ring_size_id' => $request->ring_size_id ?? null,
            'bespoke_customization_id' => $request->bespoke_customization_id ?? null,
            'bespoke_customization_types_id' => $request->bespoke_customization_types_id ?? null,
            'birth_stone_id' => $request->birth_stone_id ?? null,
            'gem_stone_id' => $request->gem_stone_id ?? null,
            'gem_stone_color_id' => $request->gem_stone_color_id ?? null,
            'engraved_text' => $request->engraved_text ?? null,
            'metal_type_karat' => $request->metal_type_karat ?? null,
        ];
    }

    public function updateCart(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'cart_id' => 'required|string', // Unique cart ID for identifying the item
            'quantity' => 'nullable|integer|min:1',
        ]);


        if ($validated->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validated->errors()], 422);
        }
        if (strpos($request->input('cart_id'), "-") !== false) {
            return response()->json(['success' => false, 'message' => 'Cart item cannot be updated'], 404);
        }
        $userID = auth()->user()->id;
        $cartId = $request->input('cart_id');
        $quantity = $request->input('quantity');

        $cartItem = Cart::session($userID)->get($cartId);
        Log::debug('Current Cart Item:', ['cart_item' => $cartItem]);

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        if ($quantity) {
            $updateData['quantity'] = [
                'relative' => false,
                'value' => $quantity,
            ];
        }

        // Update the cart item
        Cart::session($userID)->update($cartId, $updateData);

        $updatedCartItem = Cart::session($userID)->get($cartId);
        Log::debug('Updated Cart Item:', ['updated_cart_item' => $updatedCartItem]);

        return response()->json(['success' => true, 'message' => 'Cart updated successfully', 'cart_item' => $updatedCartItem]);
    }
    public function showCart()
    {
        try {
            $user = auth()->user()->id;
            // Retrieve all cart items for the user
            $cartItems = Cart::session($user)->getContent();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart is empty',
                    'cart_items' => [],
                ]);
            }

            // Format the cart data for response
            $formattedCartItems = $cartItems->map(function ($item) {
                $associatedModel = $item->associatedModel;
                $product = $associatedModel['product'] ?? null;
                $variations = $associatedModel['variation'] ?? null;

                $productImage = $associatedModel['product_image'] ?? null;

                $bespoke_type = $associatedModel['bespoke_type'] ?? null;
                $birth_stone = $associatedModel['birth_stone'] ?? null;
                $gem_stone = $associatedModel['gem_stone'] ?? null;
                return [
                    'cart_id' => $item->id, // Unique cart ID
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'attributes' => $item->attributes, // Customizables
                    'total' => $item->getPriceSum(),
                    'user_id' => auth()->user()->id,
                    'product' => $product ? [
                        'id' => $product->id,
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => $product->price,
                        'discount' => $product->discount_price,
                    ] : null,
                    'product_image' => $productImage ? [
                        'id' => $productImage->id,
                        'image' => $productImage->image,
                    ] : null,
                    'variation' => $variations ? [
                        'id' => $variations->id,
                        'price' => $variations->price,
                    ] : null,
                    'bespoke_type' => $bespoke_type ? [
                        'id' => $bespoke_type->id,
                        'name' => $bespoke_type->name,
                        'price' => $bespoke_type->price
                    ] : null,
                    'birth_stone' => $birth_stone ? [
                        'id' => $birth_stone->id,
                        'name' => $birth_stone->name,
                        'price' => $birth_stone->price
                    ] : null,
                    'gem_stone' => $gem_stone ? [
                        'id' => $gem_stone->id,
                        'name' => $gem_stone->type,
                        'price' => $gem_stone->price
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'cart_items' => $formattedCartItems,
                'cart_total' => Cart::session($user)->getTotal(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function removeCart(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'cart_id' => 'required|string', // Unique cart ID for identifying the item
        ]);

        if ($validated->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validated->errors()], 422);
        }

        $userID = auth()->user()->id;
        $cartId = $request->input('cart_id');

        // Check if the cart item exists
        $cartItem = Cart::session($userID)->get($cartId);

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        Cart::session($userID)->remove($cartId);

        return response()->json(['success' => true, 'message' => 'Cart item removed successfully']);
    }


    public function clearCart()
    {
        try {
            $userID = auth()->user()->id;

            // Clear all cart items for the user
            Cart::session($userID)->clear();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
