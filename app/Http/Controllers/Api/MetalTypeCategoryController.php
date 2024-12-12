<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetalTypeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MetalTypeCategoryController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $metalTypeCategories = MetalTypeCategory::all();

        return response()->json([
            'status' => true,
            'message' => count($metalTypeCategories) > 0 ? 'Metal types found' : 'No metal types found',
            'metal_types' => $metalTypeCategories,
        ], 200);
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'type' => 'nullable|in:P,YG,RG,WG,YP,RP',
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $metalTypeDirectory = 'step1/metal_types';


        if (!Storage::disk('public')->exists($metalTypeDirectory)) {
            Storage::disk('public')->makeDirectory($metalTypeDirectory);
        }

        // Store the image in the 'metal_types' directory on the public disk
        $imagePath = $request->file('image')->store($metalTypeDirectory, 'public');

        // Create a new MetalTypeCategory entry
        $metalTypeCategory = MetalTypeCategory::create([
            'type' => $request->type,
            'title' => $request->title,
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Metal type created successfully!',
            'metal_type' => $metalTypeCategory,
        ]);
    }

    // Display the specified resource.
    public function show($id)
    {
        $metalTypeCategory = MetalTypeCategory::find($id);

        if (!$metalTypeCategory) {
            return response()->json([
                'message' => 'Metal type not found!',
            ], 404);
        }

        $metalTypeCategory->image = $this->formatImageUrl($metalTypeCategory->image);

        return response()->json($metalTypeCategory);
    }

    // Update the specified resource in storage.
    public function update(Request $request, $id)
    {
        $valid = Validator::make($request->all(), [
            'type' => 'nullable|in:P,YG,RG,WG,YP,RP',
            'title' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }


        $metalTypeCategory = MetalTypeCategory::find($id);

        if (!$metalTypeCategory) {
            return response()->json([
                'message' => 'Metal type not found!',
            ], 404);
        }



        // If a new image is uploaded, handle the image update
        if ($request->hasFile('image')) {
            // Delete the old image if it exists
            if ($metalTypeCategory->image && Storage::disk('public')->exists($metalTypeCategory->image)) {
                Storage::disk('public')->delete($metalTypeCategory->image);
            }

            // Store the new image in the 'metal_types' directory
            $imagePath = $request->file('image')->store('step1/metal_types', 'public');
            $metalTypeCategory->image = $imagePath;
        }

        // Update type and title only if provided in the request
        $metalTypeCategory->type = $request->type ?? $metalTypeCategory->type;
        $metalTypeCategory->title = $request->title ?? $metalTypeCategory->title;

        // Save the changes
        $metalTypeCategory->save();

        return response()->json([
            'message' => 'Metal type updated successfully!',
            'metal_type' => $metalTypeCategory,
        ]);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $metalTypeCategory = MetalTypeCategory::find($id);

        if (!$metalTypeCategory) {
            return response()->json([
                'message' => 'Metal type not found!',
            ], 404);
        }

        // Delete the image if it exists
        if ($metalTypeCategory->image && Storage::disk('public')->exists($metalTypeCategory->image)) {
            Storage::disk('public')->delete($metalTypeCategory->image);
        }

        $metalTypeCategory->delete();

        return response()->json([
            'message' => 'Metal type deleted successfully!',
        ]);
    }

    // Helper function to format image URL
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'https://')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }
}
