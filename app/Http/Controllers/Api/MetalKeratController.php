<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MetalKerat;
use Illuminate\Support\Facades\Validator;

class MetalKeratController extends Controller
{
    public function index()
    {
        return response()->json(['message' => 'MetalKerat created successfully', 'data' => MetalKerat::all()], 200);
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


        $metalKerat = MetalKerat::where('id', $id);
        $metalKerat->update($request->all());
        $metal =  $metalKerat->get();

        return response()->json(['message' => 'MetalKerat updated successfully', 'data' => $metal], 200);
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
