<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Models\Clarity;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClarityRequest;
use App\Http\Requests\UpdateClarityRequest;
use Illuminate\Support\Facades\Validator;

class ClarityController extends Controller
{
    public function index()
    {
        return response()->json(Clarity::all(), 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'clarity' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $clarity = Clarity::create($request->all());

        return response()->json(['message' => 'Clarity created successfully', 'data' => $clarity], 201);
    }

    public function show($id)
    {
        $clarity = Clarity::find($id);

        if (!$clarity) {
            return response()->json(['message' => 'Clarity not found'], 404);
        }

        return response()->json($clarity, 200);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'clarity' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $clarity = Clarity::where('id', $id)->update($request->all());

        return response()->json(['message' => 'Clarity updated successfully', 'data' => $clarity], 200);
    }

    public function destroy($id)
    {
        $clarity = Clarity::find($id);

        if (!$clarity) {
            return response()->json(['message' => 'Clarity not found'], 404);
        }

        $clarity->delete();

        return response()->json(['message' => 'Clarity deleted successfully'], 200);
    }
}
