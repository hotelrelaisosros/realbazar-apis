<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BirthStone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BirthStoneController extends Controller
{

    public function index()
    {
        $BirthStones = BirthStone::all();

        $BirthStones->each(function ($BirthStone) {
            $BirthStone->image = $this->formatImageUrl($BirthStone->image); // Format image URL
        });


        return response()->json([

            'status' => true,
            'message' => count($BirthStones) > 0 ? 'BirthStones found' : 'No BirthStones found',
            'BirthStones' => $BirthStones,
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
            'price' => 'required|numeric',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $step1Directory = 'step1';
        $BirthStonesDirectory = 'step1/BirthStones';

        // Ensure directories exist
        if (!Storage::disk('public')->exists($step1Directory)) {
            Storage::disk('public')->makeDirectory($step1Directory);
        }

        if (!Storage::disk('public')->exists($BirthStonesDirectory)) {
            Storage::disk('public')->makeDirectory($BirthStonesDirectory);
        }

        // Store the image in the 'step1/BirthStones' directory on the public disk
        $imagePath = $request->file('image')->store($BirthStonesDirectory, 'public');

        // Create a new BirthStone entry
        $BirthStone = BirthStone::create([
            'name' => $request->name,
            'image' => $imagePath,
            'price' => $request->price
        ]);

        return response()->json([
            'message' => 'BirthStone created successfully!',
            'BirthStone' => $BirthStone,
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
        $BirthStone = BirthStone::findOrFail($id);
        $BirthStone->image = $this->formatImageUrl($BirthStone->image);

        return response()->json($BirthStone);
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        // Return edit form view if using a web view
    }
    public function update(Request $request, $id)
    {
        // Try to find the BirthStone, if not found return a 404 response
        $BirthStone = BirthStone::find($id);

        if (!$BirthStone) {
            // If BirthStone is not found, return a 404 with a message
            return response()->json([
                'message' => 'BirthStone not found!',
            ], 404);
        }

        // Validate the request
        $request->validate([
            'name' => 'nullable|string|max:255',  // 'nullable' in case name is not provided
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',  // Allow null for image if it's not uploaded
            'price' => 'nullable|numeric',
        ]);

        // If a new image is uploaded, handle the image update
        if ($request->hasFile('image')) {
            // Check if the current image is a remote URL
            if (filter_var($BirthStone->image, FILTER_VALIDATE_URL)) {
                // If it's a remote URL, replace it with the new image URL (from the uploaded file)
                // Store the new image in 'step1/BirthStones' directory on the public disk
                $imagePath = $request->file('image')->store('step1/BirthStones', 'public');
            } else {
                // If it's a local image, delete the old image (if it exists)
                if ($BirthStone->image && Storage::disk('public')->exists($BirthStone->image)) {
                    Storage::disk('public')->delete($BirthStone->image);
                }

                // Store the new image in 'step1/BirthStones' directory on the public disk
                $imagePath = $request->file('image')->store('step1/BirthStones', 'public');
            }
            // Update image path with the new image
            $BirthStone->image = $imagePath;
        }

        // Update name only if provided in the request
        $BirthStone->name = $request->name ?? $BirthStone->name;

        $BirthStone->price = $request->price ?? $BirthStone->price;
        // Save the changes to the database
        $BirthStone->save();

        return response()->json([
            'message' => 'BirthStone updated successfully!',
            'BirthStone' => $BirthStone,
        ]);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $BirthStone = BirthStone::find($id);

        if (!$BirthStone) {
            // If BirthStone is not found, return a 404 with a message
            return response()->json([
                'message' => 'BirthStone not found!',
            ], 404);
        }


        if ($BirthStone->image && Storage::disk('public')->exists($BirthStone->image)) {
            Storage::disk('public')->delete($BirthStone->image);
        }

        $BirthStone->delete();

        return response()->json([
            'message' => 'BirthStone deleted successfully!',
        ]);
    }
}
