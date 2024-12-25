<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GemStone;
use Illuminate\Http\Request;

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
        $validatedData = $request->validate([
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

        $gemstone = GemStone::create($validatedData);

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
        $gemstone = GemStone::find($id);

        if (!$gemstone) {
            return response()->json([
                'status' => false,
                'message' => 'Gemstone not found',
                'Gemstone' => null,
            ], 200);
        }

        $validatedData = $request->validate([
            'type' => 'nullable|in:M,LGD',
            'carat' => 'nullable|numeric',
            'shape' => 'nullable|string',
            'dimension' => 'nullable|string',
            'faceting' => 'nullable|string',
            'price' => 'nullable|numeric',
            'gemstone_color_id' => 'nullable|integer',
            'color' => 'nullable|string',
            'clarity' => 'nullable|string',
        ]);

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
