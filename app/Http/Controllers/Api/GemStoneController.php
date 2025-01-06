<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GemStone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GemStoneController extends Controller
{
    // List all gemstones
    public function index()
    {
        $gemstones = GemStone::all();

        return response()->json([
            'status' => true,
            'message' => count($gemstones) > 0 ? 'Gemstones found' : 'No gemstones found',
            'Gemstones' => $gemstones,
        ], 200);
    }

    // Create a new gemstone
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:M,LGD',
            'carat' => 'required|numeric',
            'shape' => 'required|string',
            'dimension' => 'required|string',
            'faceting' => 'required|string',
            'price' => 'required|numeric',
            'gemstone_color_id' => 'required|integer',
            'color' => 'required|string',
            'clarity' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }


        $gemstone = GemStone::create($validator->validated());

        if (!empty($gemstone)) {
            return response()->json([
                'status' => true,
                'message' => 'Gemstone created successfully',
                'Gemstone' => $gemstone,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Gemstone not created',
            ], 404);
        }
    }

    // Retrieve a single gemstone
    public function show($id)
    {
        $gemstone = GemStone::find($id);

        return response()->json([
            'status' => (bool) $gemstone,
            'message' => $gemstone ? 'Gemstone found' : 'Gemstone not found',
            'Gemstone' => $gemstone,
        ], 200);
    }

    // Update an existing gemstone
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:M,LGD',
            'carat' => 'required|numeric',
            'shape' => 'required|string',
            'dimension' => 'required|string',
            'faceting' => 'required|string',
            'price' => 'required|numeric',
            'gemstone_color_id' => 'required|integer',
            'color' => 'required|string',
            'clarity' => 'required|string',
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $validatedData = $validator->validated();
        $gemstone = GemStone::find($id);

        if (!$gemstone) {
            return response()->json([
                'status' => false,
                'message' => 'Gemstone not found',
                'Gemstone' => null,
            ], 200);
        }


        // Update only if new data is provided
        foreach ($validatedData as $key => $value) {
            if ($value !== null) {
                $gemstone->$key = $value;
            }
        }

        $gemstone->save();

        return response()->json([
            'status' => true,
            'message' => 'Gemstone updated successfully',
            'Gemstone' => $gemstone,
        ], 200);
    }

    // Delete a gemstone
    public function destroy($id)
    {
        $gemstone = GemStone::find($id);

        if (!$gemstone) {
            return response()->json([
                'status' => false,
                'message' => 'Gemstone not found',
                'Gemstone' => null,
            ], 200);
        }

        $gemstone->delete();

        return response()->json([
            'status' => true,
            'message' => 'Gemstone deleted successfully',
            'Gemstone' => null,
        ], 200);
    }
}
