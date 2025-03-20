<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MetalKerat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MetalKeratController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'MetalKerat created successfully', 'data' => MetalKerat::all()], 200);
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
        $updatedData = $request->only(['kerate', 'price', 'variant_id']);

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
