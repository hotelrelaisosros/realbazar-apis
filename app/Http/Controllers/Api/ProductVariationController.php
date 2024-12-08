<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use App\Models\Product;

use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductVariationController extends Controller
{
    private function checkProduct($productId)
    {
        $product  = Product::find($productId);
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product does not exist',]);
        }
    }
    public function index(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }


        $productVariations = ProductVariation::where('product_id', $request->product_id)->get();

        return response()->json([
            'status' => true,
            'message' => count($productVariations) > 0 ? 'Product variations found' : 'No product variations found',
            'product_variations' => $productVariations,
        ]);
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'title' => 'nullable|string',
            'size' => 'nullable|string|max:255',
            'stock' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $productVariation = ProductVariation::create($request->all());

        return response()->json([
            'message' => 'Product variation created successfully!',
            'product_variation' => $productVariation,
        ]);
    }

    public function show($id)
    {
        $productVariation = ProductVariation::find($id);

        if (!$productVariation) {
            return response()->json(['message' => 'Product variation not found!'], 404);
        }

        return response()->json($productVariation);
    }

    public function update(Request $request, $id)
    {
        $valid = Validator::make($request->all(), [
            'product_id' => 'nullable|exists:products,id',
            'size' => 'nullable|string|max:255',
            'stock' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'title' => 'nullable|string',

        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $productVariation = ProductVariation::find($id);

        if (!$productVariation) {
            return response()->json(['message' => 'Product variation not found!'], 404);
        }

        $productVariation->update($request->all());

        return response()->json([
            'message' => 'Product variation updated successfully!',
            'product_variation' => $productVariation,
        ]);
    }

    public function connectProductImage(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'variant_id' => 'required|exists:product_variations,id',
            'product_image_id' => 'required|exists:product_images,id',
        ]);

        if ($valid->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $variant = ProductImage::where('product_id', $request->product_id)->first();

        if (!$variant) {
            return response()->json(['message' => 'Product image not found!'], 404);
        }

        // if ($variant->variant_id === $request->variant_id) {
        //     return response()->json(['message' => 'Variant already connected to this product image.'], 400);
        // }

        $variant->update([
            'variant_id' => $request->variant_id
        ]);

        return response()->json(['message' => 'Product variation connected successfully!'], 200);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $productVariation = ProductVariation::find($id);

        if (!$productVariation) {
            return response()->json(['message' => 'Product variation not found!'], 404);
        }

        $productVariation->delete();

        return response()->json(['message' => 'Product variation deleted successfully!']);
    }
}
