<?php

namespace App\Http\Controllers\Api;

use App\Enums\StoneType;
use App\Helpers\ImageHelper;
use Illuminate\Http\Request;

use App\Models\Clarity;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClarityRequest;
use App\Http\Requests\UpdateClarityRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Enums\RingSize;

// Color controlled here

class ClarityController extends Controller
{
    public function index()
    {
        $clarities = Clarity::all();

        $clarities->transform(function ($clarity) {
            $clarity->image = ImageHelper::formatImageUrl($clarity->image);
            return $clarity;
        });
        return response()->json(['message' => "succesfully found colors",  'data' => $clarities], 200);
    }

    public function getVariant($id)
    {
        $validator = Validator::make(['variant_id' => $id], [
            'variant_id' => 'nullable|exists:product_variations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $clarities = DB::table('clarities')->where('variant_id', $id)->get();

        $clarities->transform(function ($clarity) {
            $clarity->image = ImageHelper::formatImageUrl($clarity->image);
            return $clarity;
        });
        return response()->json(['message' => 'Colors fetched successfully', 'data' => $clarities], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'required|numeric|min:0',
            'variant_id' => 'required|exists:product_variations,id',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $colorDirectory = 'ring2/colors';


        if (!Storage::disk('public')->exists($colorDirectory)) {
            Storage::disk('public')->makeDirectory($colorDirectory);
        }

        // Store the image in the 'metal_types' directory on the public disk
        $imagePath = $request->file('image')->store($colorDirectory, 'public');

        $clarity = Clarity::create([
            'name' => $request->name,
            'price' => $request->price,
            'variant_id' => $request->variant_id,
            'image' => $imagePath,
        ]);


        return response()->json(['message' => 'Colors created successfully', 'data' => $clarity], 201);
    }

    public function show($id)
    {
        $clarity = Clarity::find($id);

        if (!$clarity) {
            return response()->json(['message' => 'Colors not found'], 404);
        }

        return response()->json(['message' => 'color not found', 'data' => $clarity], 200);
    }

    public function update(Request $request, $id)
    {
        $clarity = Clarity::find($id);

        if (!$clarity) {
            return response()->json(['message' => 'Clarity not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Only update fields if they're present in the request
        if ($request->has('name')) {
            $clarity->name = $request->name;
        }

        if ($request->has('price')) {
            $clarity->price = $request->price;
        }

        // Handle image update
        if ($request->hasFile('image')) {
            $colorDirectory = 'ring2/colors';

            if (!Storage::disk('public')->exists($colorDirectory)) {
                Storage::disk('public')->makeDirectory($colorDirectory);
            }

            $imagePath = $request->file('image')->store($colorDirectory, 'public');
            $clarity->image = $imagePath;
        }

        $clarity->save();

        return response()->json(['message' => 'Clarity updated successfully', 'data' => $clarity], 200);
    }


    public function destroy($id)
    {
        $clarity = Clarity::find($id);

        if (!$clarity) {
            return response()->json(['message' => 'color not found'], 404);
        }

        $clarity->delete();

        return response()->json(['message' => 'Color deleted successfully'], 200);
    }

    public function showStoneType()
    {
        $stone = StoneType::TYPES;

        return response()->json([
            'message' => 'Stone Types fetched successfully',
            'data' => json_decode(json_encode($stone)) // Ensure valid JSON
        ], 200);
    }

    public function getRingSizes()
    {
        return response()->json([
            'message' => 'Stone Types fetched successfully',
            'data' => RingSize::getSizes(),
            'status' => true
        ], 200);
    }
}
