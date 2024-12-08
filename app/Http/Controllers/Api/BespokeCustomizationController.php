<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BespokeCustomization;
use App\Models\BespokeCustomizationType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BespokeCustomizationController extends Controller
{

    public function index()
    {
        $BespokeCustomizations = BespokeCustomization::all();

        $BespokeCustomizations->each(function ($BespokeCustomization) {
            $BespokeCustomization->image = $this->formatImageUrl($BespokeCustomization->image); // Format image URL
        });



        return response()->json([

            'status' => true,
            'message' => count($BespokeCustomizations) > 0 ? 'BespokeCustomizations found' : 'No BespokeCustomizations found',
            'BespokeCustomizations' => $BespokeCustomizations,
        ], 200);
    }


    // Show the form for creating a new resource.
    public function create()
    {
        // Return create form view if using a web view
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $step1Directory = 'step1';
        $BespokeCustomizationsDirectory = 'step1/BespokeCustomizations';

        // Ensure directories exist
        if (!Storage::disk('public')->exists($step1Directory)) {
            Storage::disk('public')->makeDirectory($step1Directory);
        }

        if (!Storage::disk('public')->exists($BespokeCustomizationsDirectory)) {
            Storage::disk('public')->makeDirectory($BespokeCustomizationsDirectory);
        }

        // Store the image in the 'step1/BespokeCustomizations' directory on the public disk
        $imagePath = $request->file('image')->store($BespokeCustomizationsDirectory, 'public');

        // Create a new BespokeCustomization entry
        $BespokeCustomization = BespokeCustomization::create([
            'name' => $request->name,
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'BespokeCustomization created successfully!',
            'BespokeCustomization' => $BespokeCustomization,
        ]);
    }
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'https://')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }

    public function show($id)
    {
        $BespokeCustomization = BespokeCustomization::find($id);

        if (!$BespokeCustomization) {
            return response()->json([
                'message' => 'BespokeCustomization not found!',
            ], 404);
        }
        $BespokeCustomization->image = $this->formatImageUrl($BespokeCustomization->image);


        return response()->json($BespokeCustomization);
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        // Return edit form view if using a web view
    }
    public function update(Request $request, $id)
    {
        print_r($request->all());

        $BespokeCustomization = BespokeCustomization::find($id);

        if (!$BespokeCustomization) {
            return response()->json([
                'status' => false,
                'message' => 'BespokeCustomization not found!',
            ], 404);
        }
        echo "hi";

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], 422);
        }



        if ($request->hasFile('image')) {
            echo "File Name: " . $request->file('image')->getClientOriginalName();
            if (filter_var($BespokeCustomization->image, FILTER_VALIDATE_URL)) {
                // Handle remote image replacement
                $imagePath = $request->file('image')->store('step1/BespokeCustomizations', 'public');
            } else {
                // Handle local image replacement
                if ($BespokeCustomization->image && Storage::disk('public')->exists($BespokeCustomization->image)) {
                    Storage::disk('public')->delete($BespokeCustomization->image);
                }

                $imagePath = $request->file('image')->store('step1/BespokeCustomizations', 'public');
            }

            // Update the image path
            $BespokeCustomization->image = $imagePath;
        }

        // Update the name if provided
        $BespokeCustomization->name = $request->input('name', $BespokeCustomization->name);

        // Save changes to the database
        $BespokeCustomization->save();

        return response()->json([
            'status' => true,
            'message' => 'BespokeCustomization updated successfully!',
            'data' => $BespokeCustomization,
        ]);
    }


    public function destroy($id)
    {
        $BespokeCustomization = BespokeCustomization::find($id);

        if (!$BespokeCustomization) {
            // If BespokeCustomization is not found, return a 404 with a message
            return response()->json([
                'message' => 'BespokeCustomization not found!',
            ], 404);
        }

        if ($BespokeCustomization->image && Storage::disk('public')->exists($BespokeCustomization->image)) {
            Storage::disk('public')->delete($BespokeCustomization->image);
        }

        $BespokeCustomization->delete();

        return response()->json([
            'message' => 'BespokeCustomization deleted successfully!',
        ]);
    }
}
