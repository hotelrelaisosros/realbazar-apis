<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Log;

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
            'product_image_id' => 'nullable|numeric|exists:product_images,id'
        ]);

        if ($valid->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $valid->errors(),
            ], 422);
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
        $isRing = Product::isRing($validated['product_id'])->exists();

        $customizables = [];
        if ($isRing) {
            $customizables = $this->getCustomizablesFromRequest($request);
        }

        // Determine the wishlist item ID
        $wishlistItemId = $isRing
            ? $product->id . '-' . strtoupper(substr(uniqid(), -6))
            : $product->id;

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
            'price' => $product->price,
            'quantity' => 1,
            'attributes' => $customizables,
            //  'user_id' => auth()->user()->id,
            'associatedModel' => [
                'product' => $product,
                'product_image' => $productImage,
            ],
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
                    ] : null,
                    'product_image' => $productImage ? [
                        'id' => $productImage->id,
                        'image' => $this->formatImageUrl($productImage->image),
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

    public function remove()
    {
        $user = auth()->user()->id;
        echo "user_id here : " . $user;
        Cart::session($user)->clear();

        $get_user = Cart::session($user)->getContent();
        return response()->json([
            'status' => true,
            'cart' => $get_user,
            'message' => 'Product moved to cart successfully',
        ]);
    }
}
