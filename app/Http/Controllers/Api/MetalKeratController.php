<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MetalKerat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MetalKeratController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stone_type' => 'required|string|in:LM,D',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $kerate  = DB::table('metal_kerate')->where('stone_type', $request->stone_type)->get();

        return response()->json(['status' => true, 'message' => 'MetalKerat fetched successfully', 'data' => $kerate], 200);
    }
    public function getVariant($id)
    {
        $validator = Validator::make(['variant_id' => $id], [
            'variant_id' => 'nullable|exists:product_variations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $kerate = DB::table('metal_kerate')->where('variant_id', $id)->get();

        return response()->json(['message' => 'MetalKerate fetched successfully', 'data' => $kerate], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kerate' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'stone_type' => 'required|string|in:LM,D',
            'variant_id' => 'nullable|exists:product_variations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $metalKerat = MetalKerat::create($request->all());

        return response()->json(['message' => 'MetalKerat created successfully', 'data' => $metalKerat], 201);
    }

    public function show($id)
    {
        $metalKerat = MetalKerat::find($id);

        if (!$metalKerat) {
            return response()->json(['message' => 'MetalKerat not found'], 404);
        }

        return response()->json($metalKerat, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kerate' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'stone_type' => 'required|string|in:LM,D',
            'variant_id' => 'nullable|exists:product_variations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $metalKerat = MetalKerat::find($id);
        if (!$metalKerat) {
            return response()->json(['message' => 'MetalKerat not found'], 404);
        }

        $originalData = MetalKerat::where('id', $id)->first()->toArray();
        $updatedData = $request->only(['kerate', 'price', 'variant_id', 'stone_type']);

        if (array_intersect_assoc($originalData, $updatedData) === $updatedData) {
            return response()->json(['message' => 'No changes detected'], 404);
        }

        $metalKerat->update($updatedData);

        return response()->json(['message' => 'MetalKerat updated successfully', 'data' => $metalKerat], 200);
    }


    public function destroy($id)
    {
        $metalKerat = MetalKerat::find($id);

        if (!$metalKerat) {
            return response()->json(['message' => 'MetalKerat not found'], 404);
        }

        $metalKerat->delete();

        return response()->json(['message' => 'MetalKerat deleted successfully'], 200);
    }
}
