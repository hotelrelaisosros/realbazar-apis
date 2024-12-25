<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GemStoneColor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GemStoneColorController extends Controller
{

    public function index()
    {
        $GemStoneColors = GemStoneColor::all();

        $GemStoneColors->each(function ($GemStoneColor) {
            $GemStoneColor->image = $this->formatImageUrl($GemStoneColor->image);
        });


        return response()->json([

            'status' => true,
            'message' => count($GemStoneColors) > 0 ? 'GemStoneColors found' : 'No GemStoneColors found',
            'GemStoneColors' => $GemStoneColors,
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
        $GemStoneColorsDirectory = 'step1/GemStoneColors';

        // Ensure directories exist
        if (!Storage::disk('public')->exists($step1Directory)) {
            Storage::disk('public')->makeDirectory($step1Directory);
        }

        if (!Storage::disk('public')->exists($GemStoneColorsDirectory)) {
            Storage::disk('public')->makeDirectory($GemStoneColorsDirectory);
        }

        // Store the image in the 'step1/GemStoneColors' directory on the public disk
        $imagePath = $request->file('image')->store($GemStoneColorsDirectory, 'public');

        // Create a new GemStoneColor entry
        $GemStoneColor = GemStoneColor::create([
            'name' => $request->name,
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'GemStoneColor created successfully!',
            'GemStoneColor' => $GemStoneColor,
        ]);
    }
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'https://')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }

    // Display the specified resource.
    public function show($id)
    {
        $GemStoneColor = GemStoneColor::findOrFail($id);
        $GemStoneColor->image = $this->formatImageUrl($GemStoneColor->image);

        return response()->json($GemStoneColor);
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        // Return edit form view if using a web view
    }
    public function update(Request $request, $id)
    {
        $GemStoneColor = GemStoneColor::find($id);

        if (!$GemStoneColor) {
            // If GemStoneColor is not found, return a 404 with a message
            return response()->json([
                'message' => 'GemStoneColor not found!',
            ], 404);
        }

        // Validate the request
        $request->validate([
            'name' => 'nullable|string|max:255',  // 'nullable' in case name is not provided
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',  // Allow null for image if it's not uploaded
        ]);

        // If a new image is uploaded, handle the image update
        if ($request->hasFile('image')) {
            // Check if the current image is a remote URL
            if (filter_var($GemStoneColor->image, FILTER_VALIDATE_URL)) {
                $imagePath = $request->file('image')->store('step4/GemStoneColors', 'public');
            } else {
                // If it's a local image, delete the old image (if it exists)
                if ($GemStoneColor->image && Storage::disk('public')->exists($GemStoneColor->image)) {
                    Storage::disk('public')->delete($GemStoneColor->image);
                }

                // Store the new image in 'step1/GemStoneColors' directory on the public disk
                $imagePath = $request->file('image')->store('stpep4/GemStoneColors', 'public');
            }
            // Update image path with the new image
            $GemStoneColor->image = $imagePath;
        }

        // Update name only if provided in the request
        $GemStoneColor->name = $request->name ?? $GemStoneColor->name;

        // Save the changes to the database
        $GemStoneColor->save();

        return response()->json([
            'message' => 'GemStoneColor updated successfully!',
            'GemStoneColor' => $GemStoneColor,
        ]);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $GemStoneColor = GemStoneColor::find($id);

        if (!$GemStoneColor) {
            // If GemStoneColor is not found, return a 404 with a message
            return response()->json([
                'message' => 'GemStoneColor not found!',
            ], 404);
        }


        if ($GemStoneColor->image && Storage::disk('public')->exists($GemStoneColor->image)) {
            Storage::disk('public')->delete($GemStoneColor->image);
        }

        $GemStoneColor->delete();

        return response()->json([
            'message' => 'GemStoneColor deleted successfully!',
        ]);
    }
}
