<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BespokeCustomizationType;
use App\Models\BespokeCustomization;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;


class BespokeCustomizationTypeController extends Controller
{

    // List all BespokeCustomizationTypeTypes
    public function index()
    {
        $BespokeCustomizationTypes = BespokeCustomizationType::all();
        return response()->json([

            'status' => true,
            'message' => count($BespokeCustomizationTypes) > 0 ? 'BespokeCustomizationTypes found' : 'No BespokeCustomizationTypes found',
            'BespokeCustomizationTypes' => $BespokeCustomizationTypes,
        ], 200);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'nullable|numeric',
            'type' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }

        $check_bsp = BespokeCustomization::find($request->input('type'));
        if (!$check_bsp)
            return response()->json([
                'message' => 'This customization is not found!',
            ], 404);

        // Create a new BespokeCustomizationType entry
        $BespokeCustomizationType = BespokeCustomizationType::create([
            'name' => $request->input('name'),
            'bespoke_customization_id' => $check_bsp->id,  // Fixed line
            'price' => $request->input('price'),
        ]);

        return response()->json([
            'message' => 'BespokeCustomizationType created successfully!',
            'BespokeCustomizationType' => $BespokeCustomizationType,
        ]);
    }


    public function show($id)
    {
        $BespokeCustomizationType = BespokeCustomizationType::find($id);

        if (!$BespokeCustomizationType) {
            return response()->json([
                'message' => 'BespokeCustomizationType not found!',
            ], 404);
        }
        return response()->json($BespokeCustomizationType, 202);
    }

    public function update(Request $request, $id)
    {
        $BespokeCustomizationType = BespokeCustomizationType::find($id);

        if (!$BespokeCustomizationType) {
            return response()->json([
                'status' => false,
                'message' => 'BespokeCustomizationType not found!',
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'nullable|numeric',
            'type' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }


        if ($request->filled('type')) {
            $check_bsp = BespokeCustomization::find($request->type);
            if (!$check_bsp) {
                return response()->json([
                    'status' => false,
                    'message' => 'This customization type is not found!',
                ], 404);
            }
            $BespokeCustomizationType->bespoke_customization_id = $check_bsp->id;
        }

        // Update the fields with provided data
        $BespokeCustomizationType->name = $request->input('name', $BespokeCustomizationType->name);
        $BespokeCustomizationType->price = $request->input('price', $BespokeCustomizationType->price);

        // Save the updated model
        $BespokeCustomizationType->save();

        return response()->json([
            'status' => true,
            'message' => 'BespokeCustomizationType updated successfully!',
            'data' => $BespokeCustomizationType,
        ], 200);
    }
    public function destroy($id)
    {
        $BespokeCustomizationType = BespokeCustomizationType::find($id);

        if (!$BespokeCustomizationType) {
            return response()->json([
                'message' => 'BespokeCustomizationType not found!',
            ], 404);
        }

        $BespokeCustomizationType->delete();

        return response()->json([
            'message' => 'BespokeCustomizationType deleted successfully!',
        ]);
    }
}
