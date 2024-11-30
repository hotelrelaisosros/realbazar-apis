<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Models\Product;
use App\Models\ProductImage;

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
                'request_data' => $request->all()
            ]);
        }

        $validated = $valid->validated();
        $product = Product::find($validated["product_id"]);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $productImage = $validated["product_image_id"]
            ? ProductImage::where('id', $validated["product_image_id"])->first()
            : null;

        if ($productImage) {
            $productImage->image = $this->formatImageUrl($productImage->image);
        }

        $user = auth()->user()->id;

        try {
            $wishlistObj = [
                'id' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'quantity' => 1,
                'attributes' => [],
                'associatedModel' => [
                    'product' => $product,
                    'product_image' => $productImage,
                ],
            ];

            // Adding the product to the wishlist session
            Cart::session("wishlist_$user")->add($wishlistObj);

            return response()->json(['status' => true, 'message' => 'Product added to wishlist'], 202);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function viewWishlist()
    {
        $user = auth()->user()->id;
        $wishlistItems = Cart::session("wishlist_$user")->getContent();
        $wishlistCount = $wishlistItems->count();

        return response()->json([
            'wishlistItems' => $wishlistItems,
            'wishlistCount' => $wishlistCount,
        ]);
    }
    public function removeFromWishlist(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => $valid->errors()], 422);
        }

        $user = auth()->user()->id;
        Cart::session("wishlist_$user")->remove($request->input('product_id'));

        return response()->json(['status' => true, 'message' => 'Product removed from wishlist']);
    }
    public function backToCart(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required|numeric|exists:products,id',
            'quantity' => 'required|numeric|min:1'
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => $valid->errors()], 422);
        }

        $validated = $valid->validated();
        $user = auth()->user()->id;

        $product = Product::find($validated['product_id']);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $existingCartItem = Cart::session($user)->getContent()->get($product->id);

        if ($existingCartItem) {
            Cart::session($user)->update($product->id, [
                'quantity' => $validated['quantity'],
            ]);
        } else {
            Cart::session($user)->add([
                'id' => $product->id,
                'name' => $product->title,
                'price' => $product->price,
                'quantity' => $validated['quantity'],
                'attributes' => [],
            ]);
        }

        Cart::session("wishlist_$user")->remove($validated['product_id']);

        return response()->json([
            'status' => true,
            'message' => 'Product moved to cart successfully',
        ]);
    }
}
