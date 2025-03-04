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
use App\Models\MetalKerat;
use App\Models\Clarity;



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
            'bespoke_type' => ['nullable', 'array'],
            'bespoke_type.*' => ['numeric', 'exists:bespoke_customization_types,id'],
            'birth_stone' => ['nullable', 'array'],
            'birth_stone.*' => ['numeric', 'exists:birth_stones,id'],
            'gem_stone' => 'nullable|numeric|exists:gem_stones,id',
            'metal_kerat' => 'nullable|integer|exists:metal_kerate,id',
            'clarity' => 'nullable|integer|exists:clarities,id',

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
        $initial_price = $price_counter;


        if (!empty($validated["product_image_id"])) {
            $productImage = ProductImage::find($validated["product_image_id"]);
        } elseif (!empty($validated["variation_id"])) {
            $productImage = ProductImage::where('product_id', $validated["product_id"])
                ->where('variant_id', $validated["variation_id"])->first();
        } else {
            $productImage = ProductImage::where('product_id', $validated["product_id"])
                ->first();
        }

        if ($productImage) {
            $productImage->image = $this->formatImageUrl($productImage->image);
        }

        if (!empty($validated["variation_id"])) {
            $variation = ProductVariation::find($validated["variation_id"]);
            if ($variation) {
                $price_counter +=  $variation->price ?? 0;
                $initial_price += $variation->price ?? 0;
            }
        }
        $user = auth()->user()->id;
        // Check if the product is a ring
        $isRing =  Product::isRing($validated['product_id'])->exists();

        $isBrac =  Product::isBrac($validated['product_id'])->exists();

        $metal_kerat = null;
        if (!empty($validated["metal_kerat"]) && $isBrac) {
            $metal_kerat = MetalKerat::find($validated["metal_kerat"]);
            if ($metal_kerat) {
                $price_counter += $metal_kerat->price ?? 0;
            }
        }
        $clarity  = null;
        if (!empty($validated["clarity"]) && $isBrac) {
            $clarity = Clarity::find($validated["clarity"]);
            if ($clarity) {
                $price_counter += $clarity->price ?? 0;
            }
        }

        //
        $bsp_type = [];
        if (!empty($validated["bespoke_type"]) && $isRing) {
            if (is_string($validated["bespoke_type"])) {
                $decoded = json_decode($validated["bespoke_type"]);
                $validated['bespoke_type'] = $decoded ?: [];
            }
            foreach ($validated["bespoke_type"] as $bsp) {
                $type = BespokeCustomizationType::find($bsp);
                if ($type) {
                    $bsp_type[] = $type;
                    $price_counter += $type->price ?? 0;
                }
            }
        }
        $birth_stone = [];
        if (!empty($validated["birth_stone"])  && $isRing) {
            if (is_string($validated["birth_stone"])) {
                $decoded = json_decode($validated["birth_stone"]);
                $validated['birth_stone'] = $decoded ?: [];
            }
            foreach ($validated["birth_stone"] as $birstone) {
                $stone = BirthStone::find($birstone);
                if ($stone) {
                    $birth_stone[] = $stone;
                    $price_counter += $stone->price ?? 0;
                }
            }
        }
        $gem_stone = null;
        if (!empty($validated["gem_stone"]) && $isRing) {
            $gem_stone = GemStone::find($validated["gem_stone"]);
            if ($gem_stone) {
                $price_counter += $gem_stone->price ?? 0;
            }
        }

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }


        $customizables = [];

        if ($isRing) {
            $customizables = $this->getCustomizablesFromRequest($request);
        }

        // try {
        if ($isRing) {
            if ($request["variation_id"] == null || $request["variation_id"] == "" || !isset($request["variation_id"])) {
                $cartItemId = $isRing ? $product->id . '-' . strtoupper(substr(uniqid(), -6)) : $product->id;
            } else {
                $cartItemId = $isRing ? $request["variation_id"] . '-' . strtoupper(substr(uniqid(), -6)) : $request["variation_id"];
            }
        }

        if ($isBrac) {
            if ($request["variation_id"] == null || $request["variation_id"] == "" || !isset($request["variation_id"])) {
                $cartItemId = $isBrac ? $product->id . '_' . strtoupper(substr(uniqid(), -6)) : $product->id;
            } else {
                $cartItemId = $isBrac ? $request["variation_id"] . '_' . strtoupper(substr(uniqid(), -6)) : $request["variation_id"];
            }
        }
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
        } elseif ($isBrac) {
            $models =  [
                'product' => $product,
                'product_image' => $productImage,
                'variation' => $variation,
                'kerat' => $metal_kerat ?? null,
                'clarity' => $clarity ?? null,
            ];
        } else {
            $models = [
                'product' => $product,
                'product_image' => $productImage,
                'variation' => $variation ?? [],
            ];
        }





        // Check if the product is already in the cart

        //non variant is not handled for ring  and not for brac

        if ($request["variation_id"] == null || $request["variation_id"] == "" || !isset($request["variation_id"])) {
            $existingItemNonRing = CartItem::where('user_id', $user)
                ->where('product_id', $product->id)
                ->first();
            if ($existingItemNonRing) {
                $cart = CartItem::find($existingItemNonRing->id);
                $cart->quantity += 1;
                $cart->price = $cart->quantity * $cart->initial_price;
                $cart->save();
            } else {
                $cart_item = CartItem::create([
                    'user_id' => $user,
                    'cart_id' => $cartItemId,
                    'product_id' => $product->id,
                    'name' => $product->title,
                    'price' => $price_counter,
                    'initial_price' => $initial_price,
                    'quantity' => 1,
                    'attributes' => json_encode($customizables),
                    'customizables' => json_encode($models),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {

            //with variations

            $existingItemNonRing = CartItem::where('user_id', $user)
                ->where('variant_id', $variation->id)
                ->first();


            $isRingNew = $existingItemNonRing && str_contains($existingItemNonRing->cart_id, "-");

            //here you can disable the brac quantity increase
            $isBracNew = $existingItemNonRing && str_contains($existingItemNonRing->cart_id, "_");
            if ($isBracNew) {
                // If the existing cart item has a "-" in the cart_id, create a new entry
                $cart_item = CartItem::create([
                    'user_id' => $user,
                    'cart_id' => $cartItemId,
                    'product_id' => $product->id,
                    'variant_id' => $request["variation_id"],
                    'name' => $product->title,
                    'price' => $price_counter,
                    'initial_price' => $initial_price,
                    'quantity' => 1,
                    'attributes' => json_encode($customizables),
                    'customizables' => json_encode($models),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else if ($isRingNew) {
                // If the existing cart item has a "-" in the cart_id, create a new entry
                $cart_item = CartItem::create([
                    'user_id' => $user,
                    'cart_id' => $cartItemId,
                    'product_id' => $product->id,
                    'variant_id' => $request["variation_id"],
                    'name' => $product->title,
                    'price' => $price_counter,
                    'initial_price' => $initial_price,
                    'quantity' => 1,
                    'attributes' => json_encode($customizables),
                    'customizables' => json_encode($models),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($existingItemNonRing) {
                $cart = CartItem::find($existingItemNonRing->id);
                $cart->quantity += 1;
                $cart->price = $cart->quantity * $cart->initial_price;
                $cart->save();
            } else {
                // If no existing cart item, create a new one for non ring 
                $cart_item = CartItem::create([
                    'user_id' => $user,
                    'cart_id' => $cartItemId,
                    'product_id' => $product->id,
                    'variant_id' => $request["variation_id"],
                    'initial_price' => $price_counter,
                    'name' => $product->title,
                    'price' => $price_counter,
                    'quantity' => 1,
                    'attributes' => json_encode($customizables),
                    'customizables' => json_encode($models),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Dispatch remove cart item after 48 hours (optional)
        if (isset($cart_item)) {
            // RemoveCartItem::dispatch($user, $cart_item->id)->delay(now()->addDays(5));
        }

        return response()->json(['status' => true, 'Message' => 'Product added to cart', "cart" => $cart_item ?? $existingItemNonRing], 202);
        // } catch (Exception $e) {
        //     return response()->json(['status' => false, 'Message' => $e->getMessage()], 500);
        // }
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
            'faceting_id' => $request->faceting_id ?? null,

        ];
    }




    public function updateCartTable(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:cart_items,id', // Unique cart ID for identifying the item
            'cart_id' => 'required|string|exists:cart_items,cart_id', // Unique cart ID for identifying the item
            'quantity' => 'nullable|integer|min:1',
        ]);

        if ($validated->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validated->errors()], 422);
        }

        if (strpos($request->input('cart_id'), "-") !== false) {
            return response()->json(['success' => false, 'message' => 'Cart item cannot be updated'], 404);
        }

        $userID = auth()->user()->id;
        $cartId = $request['cart_id'];
        $quantity = $request['quantity'];

        // Check the cart item in the database table
        $cartItemTable = CartItem::where('user_id', $userID)
            ->where('id', $request['id'])
            ->where('cart_id', $cartId)
            ->first();

        if (str_contains($cartItemTable->cart_id, "-") || str_contains($cartItemTable->cart_id, "_")) {
            return response()->json(['success' => false, 'message' => 'Cart item cannot be updated  as it is customized product'], 404);
        }
        if (!$cartItemTable) {
            return response()->json(['success' => false, 'message' => 'Cart item not found in table'], 404);
        }

        if ($quantity) {
            $cartItemTable->quantity = $quantity;
            $cartItemTable->price = $quantity * $cartItemTable->initial_price; // Update price based on quantity
        }

        $cartItemTable->save();



        return response()->json([
            'success' => true,
            'message' => 'Cart updated in table successfully',
            'table' => $cartItemTable,
        ]);
    }


    public function showCartTable()
    {
        if (!auth()->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $user = auth()->user()->id;

        // Retrieve all cart items for the user from the database
        $cartItemsTable = CartItem::where('user_id', $user)->get();

        if ($cartItemsTable->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart is empty in the table',
                'cart_items_table' => [],
            ]);
        }

        // Format the cart data from the table
        $formattedCartItemsTable = $cartItemsTable->map(function ($item) {
            $associatedModel = json_decode($item->customizables, true);

            return [
                'id' => $item->id,
                'cart_id' => $item->cart_id,
                'name' => $item->name,
                'price' => $item->price,
                'per_unit_price' => $item->initial_price,
                "customization_price" => $item->price -  $item->initial_price,
                'quantity' => $item->quantity,
                'attributes' => json_decode($item->attributes, true),
                'total' => $item->price * $item->quantity,
                'user_id' => $item->user_id,
                'product' => isset($associatedModel['product']) ? [
                    'id' => $associatedModel['product']['id'] ?? null,
                    'title' => $associatedModel['product']['title'] ?? null,
                    'description' => $associatedModel['product']['desc'] ?? null,
                    'price' => $associatedModel['product']['price'] ?? null,
                    'discount' => $associatedModel['product']['discount_price'] ?? null,
                ] : null,
                'product_image' => isset($associatedModel['product_image']) ? [
                    'id' => $associatedModel['product_image']['id'] ?? null,
                    'image' => $associatedModel['product_image']['image'] ?? null,
                ] : null,
                'variation' => isset($associatedModel['variation']) ? [
                    'id' => $associatedModel['variation']['id'] ?? null,
                    'title' => $associatedModel['variation']['title'] ?? null,
                    'size' => $associatedModel['variation']['size'] ?? null,
                    'stock' => $associatedModel['variation']['stock'] ?? null,
                    'price' => $associatedModel['variation']['price'] ?? null,
                ] : null,
                'bespoke_types' => isset($associatedModel['bespoke_type']) ? array_map(function ($type) {
                    return [
                        'id' => $type['id'] ?? null,
                        'name' => $type['name'] ?? null,
                        'price' => $type['price'] ?? null,
                    ];
                }, $associatedModel['bespoke_type']) : [],
                'birth_stones' => isset($associatedModel['birth_stone']) ? array_map(function ($stone) {
                    return [
                        'id' => $stone['id'] ?? null,
                        'name' => $stone['name'] ?? null,
                        'price' => $stone['price'] ?? null,
                        'image' => $stone['image'] ?? null,
                    ];
                }, $associatedModel['birth_stone']) : [],
                'gem_stone' => isset($associatedModel['gem_stone']) ? [
                    'id' => $associatedModel['gem_stone']['id'] ?? null,
                    'type' => $associatedModel['gem_stone']['type'] ?? null,
                    'carat' => $associatedModel['gem_stone']['carat'] ?? null,
                    'price' => $associatedModel['gem_stone']['price'] ?? null,
                    'color' => $associatedModel['gem_stone']['color'] ?? null,
                    'clarity' => $associatedModel['gem_stone']['clarity'] ?? null,
                ] : null,
                'kerat' => isset($associatedModel['kerat']) ? [
                    'id' => $associatedModel['kerat']['id'] ?? null,
                    'kerat' => $associatedModel['kerat']['kerate'] ?? null,
                    'price' => $associatedModel['kerat']['price'] ?? null,
                ] : null,
                'clarity' => isset($associatedModel['clarity']) ? [
                    'id' => $associatedModel['clarity']['id'] ?? null,
                    'clarity' => $associatedModel['clarity']['clarity'] ?? null,
                    'price' => $associatedModel['clarity']['price'] ?? null,
                ] : null,
            ];
        });

        // Return the formatted response
        return response()->json([
            'success' => true,
            'cart_items_table' => $formattedCartItemsTable,
        ]);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to retrieve cart from table',
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }
    }


    public function removeCartTable(Request $request)
    {
        if (!auth()->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }
        $validated = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:cart_items,id', // Unique cart ID for identifying the item
        ]);

        if ($validated->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validated->errors()], 422);
        }

        $userID = auth()->user()->id;
        $cartId = $request['id'];

        // Check if the cart item exists in the database table
        $cartItemTable = CartItem::where('user_id', $userID)
            ->where('id', $cartId)
            ->first();

        if (!$cartItemTable) {
            return response()->json(['success' => false, 'message' => 'Cart item not found in table'], 404);
        }

        // Remove the cart item from the database table
        $cartItemTable->delete();

        return response()->json(['success' => true, 'message' => 'Cart item removed from table successfully']);
    }


    public function clearCartTable()
    {
        try {
            $userID = auth()->user()->id;

            // Clear all cart items from the database table for the user
            CartItem::where('user_id', $userID)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared from table successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart from table',
                'error' => $e->getMessage(),
            ], 500);
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

    public function showCartSession()
    {
        if (!auth()->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        try {
            $user = auth()->user()->id;
            // Retrieve all cart items for the user from the session
            $cartItems = Cart::session($user)->getContent();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart is empty in the session',
                    'cart_items_session' => [],
                ]);
            }

            // Format the cart data from the session
            $formattedCartItemsSession = $cartItems->map(function ($item) {
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
                    'bespoke_types' => collect($bespoke_type)->map(function ($type) {
                        return [
                            'id' => $type->id,
                            'name' => $type->name,
                            'price' => $type->price,
                        ];
                    })->toArray(),
                    'birth_stones' => collect($birth_stone)->map(function ($stone) {
                        return [
                            'id' => $stone->id,
                            'name' => $stone->name,
                            'price' => $stone->price,
                        ];
                    })->toArray(),
                    'gem_stone' => $gem_stone ? [
                        'id' => $gem_stone->id,
                        'name' => $gem_stone->type,
                        'price' => $gem_stone->price
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Cart retrieved from session successfully',
                'cart_items_session' => $formattedCartItemsSession,
                'cart_total_session' => Cart::session($user)->getTotal(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cart from session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    public function updateCartSession(Request $request)
    {
        if (!auth()->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }
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

        // Check the cart item in the session
        $cartItemSession = Cart::session($userID)->get($cartId);
        Log::debug('Current Cart Item in Session:', ['cart_item' => $cartItemSession]);

        if (!$cartItemSession) {
            return response()->json(['success' => false, 'message' => 'Cart item not found in session'], 404);
        }

        // Prepare the update data
        if ($quantity) {
            $updateData['quantity'] = [
                'relative' => false,
                'value' => $quantity,
            ];
        }

        // Update the cart item in the session
        Cart::session($userID)->update($cartId, $updateData);

        // Fetch the updated cart item from session
        $updatedCartItemSession = Cart::session($userID)->get($cartId);
        Log::debug('Updated Cart Item in Session:', ['updated_cart_item' => $updatedCartItemSession]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated in session successfully',
            'session' => $updatedCartItemSession,
        ]);
    }
    public function removeCartSession(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'cart_id' => 'required|string', // Unique cart ID for identifying the item
        ]);

        if ($validated->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validated->errors()], 422);
        }

        $userID = auth()->user()->id;
        $cartId = $request->input('cart_id');

        // Check if the cart item exists in the session
        $cartItemSession = Cart::session($userID)->get($cartId);

        if (!$cartItemSession) {
            return response()->json(['success' => false, 'message' => 'Cart item not found in session'], 404);
        }

        // Remove the cart item from the session
        Cart::session($userID)->remove($cartId);

        return response()->json(['success' => true, 'message' => 'Cart item removed from session successfully']);
    }

    public function clearCartSession()
    {
        try {
            $userID = auth()->user()->id;

            // Clear all cart items from the session for the user
            Cart::session($userID)->clear();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared from session successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart from session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
