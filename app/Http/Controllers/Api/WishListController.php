<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Darryldecode\Cart\Facades\CartFacade as Cart;
use App\Models\Product;


class WishListController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $userID = 2;
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        // Retrieve product details from the database by product ID
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Adding item to the wishlist
        Cart::session('wishlist')->add([
            'id' => $productId,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $quantity,
            'attributes' => [],
        ]);
        Cart::session($userID)->remove($productId);

        $redirectUrl = '/wishlist';
        return response()->json([
            'message' => 'Product added to wishlist successfully',
            'redirect_url' => $redirectUrl
        ]);
    }

    public function viewWishlist()
    {
        $userID = 2;
        $cartContent = Cart::session($userID)->getContent();
        $cartCount = $cartContent->count();
        // Retrieving items from the wishlist
        $wishlistItems = Cart::session('wishlist')->getContent();
        $wishlistCount = $wishlistItems->count();

        return view('wishlist')
            ->with('cartCount', $cartCount)
            ->with('wishlistCount', $wishlistCount)
            ->with('wishlistItems', $wishlistItems);
    }

    public function removeFromWishlist(Request $request)
    {
        $userID = 1;
        $productId = $request->input('product_id');
        // Removing item from the wishlist
        Cart::session('wishlist')->remove($productId);
    }

    public function backToCart(Request $request)
    {
        $userID = 2;
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        $product = Product::find($productId);
        $cartItems = Cart::session($userID)->getContent();

        $existingCartItem = $cartItems->get($productId);

        if ($existingCartItem) {
            // If the product is already in the cart, update the quantity
            Cart::session($userID)->update($productId, array(
                'quantity' =>  $quantity
            ));
            $message = 'Item updated in the cart successfully.';
        } else {
            // If the product is not in the cart, add it to the cart
            Cart::session($userID)->add($product->id, $product->name, $product->price, $quantity);
            $message = 'Item added to the cart successfully.';
        }

        Cart::session('wishlist')->remove($productId);

        $redirectUrl = '/';
        return response()->json([
            'message' => $message,
            'redirect_url' => $redirectUrl
        ]);
    }
}
