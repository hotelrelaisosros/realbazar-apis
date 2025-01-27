<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Log;
use App\Models\ProductVariation;
use App\Models\BespokeCustomizationType;
use App\Models\GemStone;
use App\Models\BirthStone;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\CartItem;
use App\Jobs\RemoveWishlistItem;
use App\Models\Wishlist;

class WishListController extends Controller
{
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'http')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
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



    public function addToWishlist(Request $request)
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

        $user = auth()->user()->id;
        // Check if the product is a ring
        $isRing =  Product::isRing($validated['product_id'])->exists();

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

        if ($request["variation_id"] == null || $request["variation_id"] == "" || !isset($request["variation_id"])) {
            $cartItemId = $isRing ? $product->id . '-' . strtoupper(substr(uniqid(), -6)) : $product->id;
        } else {
            $cartItemId = $isRing ? $request["variation_id"] . '-' . strtoupper(substr(uniqid(), -6)) : $request["variation_id"];
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
        } else {
            $models = [
                'product' => $product,
                'product_image' => $productImage,
                'variation' => $variation ?? [],
            ];
        }

        if ($request["variation_id"] == null || $request["variation_id"] == "" || !isset($request["variation_id"])) {
            $existingItemNonRing = Wishlist::where('user_id', $user)
                ->where('product_id', $product->id)
                ->first();
            if ($existingItemNonRing) {
                return response()->json(['status' => true, 'message' => 'Product already in wishlist'], 404);
            } else {
                $cart_item = Wishlist::create([
                    'user_id' => $user,
                    'cart_id' => $cartItemId,
                    'product_id' => $product->id,
                    'name' => $product->title,
                    'price' => $price_counter,
                    'attributes' => json_encode($customizables),
                    'customizables' => json_encode($models),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {

            //with variations

            $existingItemNonRing = Wishlist::where('user_id', $user)
                ->where('variant_id', $variation->id)
                ->first();


            $isRingNew = $existingItemNonRing && str_contains($existingItemNonRing->cart_id, "-");

            if ($isRingNew) {
                // If the existing cart item has a "-" in the cart_id, create a new entry
                $cart_item = Wishlist::create([
                    'user_id' => $user,
                    'cart_id' => $cartItemId,
                    'product_id' => $product->id,
                    'variant_id' => $request["variation_id"],
                    'name' => $product->title,
                    'price' => $price_counter,
                    'initial_price' => $price_counter,
                    'quantity' => 1,
                    'attributes' => json_encode($customizables),
                    'customizables' => json_encode($models),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } elseif ($existingItemNonRing) {
                return response()->json(['status' => true, 'message' => 'Product already in wishlist'], 404);
            } else {
                // If no existing cart item, create a new one
                $cart_item = Wishlist::create([
                    'user_id' => $user,
                    'cart_id' => $cartItemId,
                    'product_id' => $product->id,
                    'variant_id' => $request["variation_id"],
                    'name' => $product->title,
                    'price' => $price_counter,
                    'attributes' => json_encode($customizables),
                    'customizables' => json_encode($models),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Dispatch remove cart item after 48 hours (optional)
        if (isset($cart_item)) {
            // RemoveWishlistItem::dispatch($user, $cart_item->id)->delay(now()->addDays(5));
        }

        return response()->json(['status' => true, 'Message' => 'Product added to wishlist', "wishlist" => $cart_item ?? $existingItemNonRing ?? []], 202);
        // } catch (Exception $e) {
        //     return response()->json(['status' => false, 'Message' => $e->getMessage()], 500);
        // }
    }

    public function viewWishlist()
    {
        if (!auth()->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $user = auth()->user()->id;

        // Retrieve all cart items for the user from the database
        $cartItemsTable = Wishlist::where('user_id', $user)->get();

        if ($cartItemsTable->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Wishlist is empty in the table',
                'wishlist' => [],
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
                'attributes' => json_decode($item->attributes, true),
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
            ];
        });

        // Return the formatted response
        return response()->json([
            'success' => true,
            'wishlist' => $formattedCartItemsTable,
        ], 200);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed to retrieve cart from table',
        //         'error' => $e->getMessage(),
        //     ], 500);
        // }
    }

    public function removeAllFromWishlist(Request $request)
    {
        if (!auth()->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }
        $userID = auth()->user()->id;

        // Retrieve all wishlist items for the user
        $wishlist = Wishlist::where('user_id', $userID)->get();

        if ($wishlist->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Wishlist is already empty',
            ]);
        }

        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'All items removed from wishlist successfully',
        ], 200);
    }


    public function removeFromWishlist(Request $request)
    {
        if (!auth()->user()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }
        $validated = Validator::make($request->all(), [
            'id' => 'required|numeric|exists:wishlists,id', // Unique cart ID for identifying the item
        ]);

        if ($validated->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validated->errors()], 422);
        }

        $userID = auth()->user()->id;
        $cartId = $request['id'];

        // Check if the cart item exists in the database table
        $cartItemTable = Wishlist::where('user_id', $userID)
            ->where('id', $cartId)
            ->first();

        if (!$cartItemTable) {
            return response()->json(['success' => false, 'message' => 'Wishlist item not found in table'], 404);
        }

        // Remove the cart item from the database table
        $cartItemTable->delete();

        return response()->json(['success' => true, 'message' => 'Wishlist item removed from table successfully']);
    }



    public function backToCart(Request $request)
    {
        if (!auth()->user()) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $valid = Validator::make($request->all(), [
            'wishlist_id' => 'required|numeric|exists:wishlists,id',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => $valid->errors()], 422);
        }
        $validated = $valid->validated();
        $user = auth()->user()->id;
        $wishlistItem = Wishlist::where('id', $validated['wishlist_id'])->first();
        if (!$wishlistItem) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found in wishlist',
            ], 404);
        }
        $cart = CartItem::create([
            'id' => $wishlistItem->id,
            'user_id' => $user,
            'cart_id' => $wishlistItem->cart_id,
            'product_id' => $wishlistItem->product_id,
            'variant_id' => $wishlistItem->variant_id,
            'customizables' => $wishlistItem->customizables,
            'name' => $wishlistItem->name,
            'price' => $wishlistItem->price,
            'initial_price' => $wishlistItem->price,
            'attributes' => $wishlistItem->attributes,
            'associatedModel' => $wishlistItem->associatedModel,
            'quantity' => 1,
        ]);

        $wishlistItem->delete();

        return response()->json([
            'status' => true,
            'cart' => $cart,
            'wishlist' => $wishlistItem,
            'message' => 'Product moved to cart successfully',
        ]);
    }
}
