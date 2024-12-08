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

class CartController extends Controller
{
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'https://')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }
    public function addToCart(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
            'product_image_id' => 'nullable|numeric|exists:product_images,id'
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



        if ($validated["product_image_id"]) {
            $productImage = ProductImage::where('id', $validated["product_image_id"])->first();
            $productImage->image = $this->formatImageUrl($productImage->image);
        } else {
            $productImage = [];
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
            // Determine the cart item ID
            // If it's a ring, use product ID with a unique custom ID, else just use product ID

            $cartItemId = $isRing ? $product->id . '-' . strtoupper(substr(uniqid(), -6)) : $product->id;

            // Create the cart object
            $cartObj = [
                'id' => $cartItemId, // Use unique id for rings, just product id for non-rings
                'name' => $product->title,
                'price' => $product->price,
                'quantity' => 1,
                'attributes' => $customizables, // Only rings will have attributes
                'associatedModel' => [
                    'product' => $product,
                    'product_image' => $productImage
                ],
            ];

            // Check if the product is already in the cart
            Cart::session($user)->getContent();

            // Adding item to cart
            Cart::session($user)->add($cartObj);
            Log::info('Cart in session:', ['cart_items' => Cart::session($user)->getContent()]);

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
            'cart_id' => 'required|string', // Unique cart ID for identifying the item
            'fields_to_nullify' => 'required|array', // Array of fields to be nullified
            'fields_to_nullify.*' => 'in:bespoke_customization,bespoke_customization_types,birth_stone,gem_stone',
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

        // Update specified fields to null in attributes
        $updatedAttributes = $cartItem->attributes;

        foreach ($fieldsToNullify as $field) {
            if ($updatedAttributes->has($field)) { // Use `has` instead of `array_key_exists`
                $updatedAttributes[$field] = null; // Set the value to null
            }
        }

        // Update the cart item with the modified attributes
        Cart::session($userID)->update($cartId, [
            'attributes' => $updatedAttributes
        ]);

        // Fetch the updated cart item
        $updatedCartItem = Cart::session($userID)->get($cartId);

        return response()->json([
            'success' => true,
            'message' => 'Customizations updated successfully',
            'cart_item' => $updatedCartItem
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
                $productImage = $associatedModel['product_image'] ?? null;

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
                    ] : null,
                    'product_image' => $productImage ? [
                        'id' => $productImage->id,
                        'image' => $productImage->image,
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
