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


class WishListController extends Controller
{
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'https://')) {
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
        $isRing = Product::isRing($validated['product_id'])->exists();

        $customizables = [];
        if ($isRing) {
            $customizables = $this->getCustomizablesFromRequest($request);
        }

        // Determine the wishlist item ID
        $wishlistItemId = $isRing
            ? $product->id . '-' . strtoupper(substr(uniqid(), -6))
            : $product->id;


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

        // Check if the product is already in the wishlist
        $existingWishlistItem = Cart::session("wishlist_$user")->getContent()->get($wishlistItemId);

        if ($existingWishlistItem) {
            return response()->json([
                'status' => false,
                'message' => 'Product already in wishlist',
            ]);
        }

        // Add the product to the wishlist
        Cart::session("wishlist_$user")->add([
            'id' => $wishlistItemId,
            'name' => $product->title,
            'price' => $price_counter,
            'quantity' => 1,
            'attributes' => $customizables,
            //  'user_id' => auth()->user()->id,
            'associatedModel' => $models,
        ]);

        Log::info("lola" . Cart::session("wishlist_$user")->getContent());
        // Log::info('Raw Cart Session:', session()->get('cartalyst.cart'));

        return response()->json([
            'status' => true,
            'message' => 'Product added to wishlist successfully',
        ]);
    }


    public function viewWishlist()
    {
        try {
            $user = auth()->user();

            // Retrieve all wishlist items for the user
            $wishlistItems = Cart::session("wishlist_$user->id")->getContent();

            if ($wishlistItems->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Wishlist is empty',
                    'wishlist_items' => [],
                ]);
            }

            // Format the wishlist data for response
            $formattedWishlistItems = $wishlistItems->map(function ($item) {
                $associatedModel = $item->associatedModel;
                $product = $associatedModel['product'] ?? null;
                $productImage = $associatedModel['product_image'] ?? null;
                $variations = $associatedModel['variation'] ?? null;

                return [
                    'wishlist_id' => $item->id, // Unique wishlist ID
                    'name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'attributes' => $item->attributes, // Customizables
                    'total' => $item->getPriceSum(),

                    'product' => $product ? [
                        'id' => $product->id,
                        'title' => $product->title,
                        'description' => $product->description,
                        'price' => $product->price,
                        'discount' => $product->discount->price,
                    ] : null,
                    'product_image' => $productImage ? [
                        'id' => $productImage->id,
                        'image' => $this->formatImageUrl($productImage->image),
                    ] : null,
                    'variation' => $variations ? [
                        'id' => $variations->id,
                        'price' => $variations->price,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Wishlist retrieved successfully',
                'wishlist_items' => $formattedWishlistItems,
                'wishlist_count' => $formattedWishlistItems->count(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve wishlist',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function removeAllFromWishlist(Request $request)
    {
        $userID = auth()->user()->id;

        // Retrieve all wishlist items for the user
        $wishlistItems = Cart::session("wishlist_$userID")->getContent();

        if ($wishlistItems->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Wishlist is already empty',
            ]);
        }

        // Remove all items from the wishlist
        Cart::session("wishlist_$userID")->clear();

        return response()->json([
            'success' => true,
            'message' => 'All items removed from wishlist successfully',
        ]);
    }


    public function removeFromWishlist(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'wishlist_id' => 'required|string', // Unique cart ID for identifying the item
        ]);

        if ($validated->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validated->errors()], 422);
        }

        $userID = auth()->user()->id;
        $cartId = $request->input('wishlist_id');

        $wishlistItem = Cart::session("wishlist_$userID")->get($cartId);

        if (!$wishlistItem) {
            return response()->json(['success' => false, 'message' => 'Wishlist item not found'], 404);
        }

        // Remove the item from the wishlist
        Cart::session("wishlist_$userID")->remove($cartId);

        return response()->json(['success' => true, 'message' => 'Wishlist item removed successfully']);
    }



    public function backToCart(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'wishlist_id' => 'required|string',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => $valid->errors()], 422);
        }

        $validated = $valid->validated();
        $user = auth()->user()->id;

        // Check if the wishlist item exists
        $wishlistItem = Cart::session("wishlist_$user")->get($validated['wishlist_id']);

        if (!$wishlistItem) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found in wishlist',
            ], 404);
        }


        if (str_contains($wishlistItem->id, "-")) {
            if (Cart::session($user)->get($wishlistItem->id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product already moved to cart as it cannot be moved again',
                ], 404);
            }
        }
        // if ($existingCartItem) {
        //     Cart::session($user)->update($productId, array(
        //         'quantity' =>  $wishlistItem->quantity + 1
        //     ));
        //     $message = 'Item updated in the cart successfully.';
        // } else {
        //     // If the product is not in the cart, add it to the cart
        //     \Cart::session($userID)->add($product->id, $product->name, $product->price, $quantity);
        //     $message = 'Item added to the cart successfully.';
        // }

        Cart::session($user)->add([
            'id' => $wishlistItem->id,
            'name' => $wishlistItem->name,
            'price' => $wishlistItem->price,
            'quantity' => $wishlistItem->quantity,
            'attributes' => $wishlistItem->attributes,
            'associatedModel' => $wishlistItem->associatedModel,
        ]);


        $get_user = Cart::session($user)->getContent();

        Cart::session("wishlist_$user")->remove($validated['wishlist_id']);
        $wishlist = Cart::session("wishlist_$user")->getContent();



        return response()->json([
            'status' => true,
            'cart ' => $get_user,
            'wishlist' => $wishlist,
            'message' => 'Product moved to cart successfully',
        ]);
    }
}
