<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Gemshape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GemshapeController extends Controller
{
    // Display a listing of the resource.
    public function index()
    {
        $gemshapes = Gemshape::all();

        // $gemshapes->each(function ($gemshape) {
        //     $gemshape->image = $this->formatImageUrl($gemshape->image); // Format image URL
        // });


        return response()->json([

            'status' => true,
            'message' => count($gemshapes) > 0 ? 'gemshapes found' : 'No gemshapes found',
            'gemshapes' => $gemshapes,
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

        $valid = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }

        $step1Directory = 'step1';
        $gemshapesDirectory = 'step1/gemshapes';

        // Ensure directories exist
        if (!Storage::disk('public')->exists($step1Directory)) {
            Storage::disk('public')->makeDirectory($step1Directory);
        }

        if (!Storage::disk('public')->exists($gemshapesDirectory)) {
            Storage::disk('public')->makeDirectory($gemshapesDirectory);
        }

        // Store the image in the 'step1/gemshapes' directory on the public disk
        $imagePath = $request->file('image')->store($gemshapesDirectory, 'public');

        // Create a new Gemshape entry
        $gemshape = Gemshape::create([
            'name' => $request->name,
            'image' => $imagePath,
        ]);

        return response()->json([
            'message' => 'Gemshape created successfully!',
            'gemshape' => $gemshape,
        ]);
    }
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'http')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }

    // Display the specified resource.
    public function show($id)
    {
        $gemshape = Gemshape::find($id);

        if (!$gemshape) {
            return response()->json([
                'message' => 'Gemshape not found!',
            ], 404);
        }
        $gemshape->image = $this->formatImageUrl($gemshape->image);

        return response()->json($gemshape);
    }

    // Show the form for editing the specified resource.
    public function edit($id)
    {
        // Return edit form view if using a web view
    }
    public function update(Request $request, $id)
    {
        // Try to find the gemshape, if not found return a 404 response
        $gemshape = Gemshape::find($id);

        if (!$gemshape) {
            // If gemshape is not found, return a 404 with a message
            return response()->json([
                'message' => 'Gemshape not found!',
            ], 404);
        }

        $valid = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => false, 'Message' => 'Validation errors', 'errors' => $valid->errors()]);
        }
        // If a new image is uploaded, handle the image update
        if ($request->hasFile('image')) {
            // Check if the current image is a remote URL
            if (filter_var($gemshape->image, FILTER_VALIDATE_URL)) {
                // If it's a remote URL, replace it with the new image URL (from the uploaded file)
                // Store the new image in 'step1/gemshapes' directory on the public disk
                $imagePath = $request->file('image')->store('step1/gemshapes', 'public');
            } else {
                // If it's a local image, delete the old image (if it exists)
                if ($gemshape->image && Storage::disk('public')->exists($gemshape->image)) {
                    Storage::disk('public')->delete($gemshape->image);
                }

                // Store the new image in 'step1/gemshapes' directory on the public disk
                $imagePath = $request->file('image')->store('step1/gemshapes', 'public');
            }
            // Update image path with the new image
            $gemshape->image = $imagePath;
        }

        // Update name only if provided in the request
        $gemshape->name = $request->name ?? $gemshape->name;

        // Save the changes to the database
        $gemshape->save();

        return response()->json([
            'message' => 'Gemshape updated successfully!',
            'gemshape' => $gemshape,
        ]);
    }

    // Remove the specified resource from storage.
    public function destroy($id)
    {
        $gemshape = Gemshape::find($id);

        if (!$gemshape) {
            // If gemshape is not found, return a 404 with a message
            return response()->json([
                'message' => 'Gemshape not found!',
            ], 404);
        }

        if ($gemshape->image && Storage::disk('public')->exists($gemshape->image)) {
            Storage::disk('public')->delete($gemshape->image);
        }

        $gemshape->delete();

        return response()->json([
            'message' => 'Gemshape deleted successfully!',
        ]);
    }
}
