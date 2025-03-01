<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Adress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    // ✅ Get Single Address for Authenticated User
    public function getAddress($id)
    {
        $user = auth()->user();

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:adresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $address = $user->addresses()->where('id', $id)->get();

        if ($address->isEmpty()) {
            return response()->json(['message' => 'Address not found'], 404);
        }
        return response()->json(['address' => $address]);
    }

    // ✅ Get All Addresses for Authenticated User
    public function getAllAddresses()
    {
        $user = auth()->user();

        $addresses = $user->addresses()->where('is_primary', true)->first();
        if ($addresses->isEmpty()) {
            return response()->json(['message' => 'Address not found'], 404);
        }
        return response()->json(['addresses' => $addresses]);
    }


    // ✅ Create New Address
    public function createAddress(Request $request)
    {
        echo auth()->user()->id;
        $validator = Validator::make($request->all(), [
            'surname'           => 'required|string|max:255',
            'first_name'        => 'required|string|max:255',
            'last_name'         => 'required|string|max:255',
            'address'           => 'required|string|max:255',
            'street_name'       => 'nullable|string|max:255',
            'street_number'     => 'required|string|max:10',
            'lat'               => 'nullable|string|max:255',
            'lon'               => 'nullable|string|max:255',
            'address2'          => 'nullable|string|max:255',
            'country'           => 'required|string|max:2',
            'city'              => 'required|string|max:255',
            'zip'               => 'required|string|max:20',
            'phone'             => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'is_primary'        => 'boolean',
        ]);

        if ($request->is_primary) {
            $check_primary = Adress::where('user_id', auth()->user()->id)->where('is_primary', true)->first();
            if ($check_primary) {
                return response()->json(['message' => 'Default address already exists. Change is_primary parameter'], 400);
            }
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Add user_id to the validated data
        $validatedData = $validator->validated();
        $validatedData['user_id'] = auth()->user()->id;

        // Ensure Address model is spelled correctly
        $address = Adress::create($validatedData);

        return response()->json([
            'message' => 'Address created successfully',
            'address' => $address
        ], 201);
    }

    // ✅ Update an Address
    public function updateAddress(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $address = Adress::where('user_id', $user->id)->where('id', $id)->first();
        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'surname'           => 'sometimes|required|string|max:255',
            'first_name'        => 'sometimes|required|string|max:255',
            'last_name'         => 'sometimes|required|string|max:255',
            'address'           => 'sometimes|required|string|max:255',
            'street_name'       => 'nullable|string|max:255',
            'street_number'     => 'sometimes|required|string|max:10',
            'lat'               => 'nullable|string|max:255',
            'lon'               => 'nullable|string|max:255',
            'address2'          => 'nullable|string|max:255',
            'country'           => 'sometimes|required|string|max:2',
            'city'              => 'sometimes|required|string|max:255',
            'zip'               => 'sometimes|required|string|max:20',
            'phone'             => 'nullable|string|max:20',
            'phone_country_code' => 'nullable|string|max:5',
            'is_primary'        => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // If setting as primary, unset other primary addresses for this user
        if (isset($validatedData['is_primary']) && $validatedData['is_primary']) {
            $user->addresses()->update(['is_primary' => false]);
        }

        $address->update($validatedData);

        return response()->json([
            'message' => 'Address updated successfully',
            'address' => $address
        ], 200);
    }


    // ✅ Delete an Address
    public function deleteAddress($id)
    {

        $address = Adress::where('user_id', auth()->user()->id)->where('id', $id)->first();

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $address->delete();
        return response()->json(['message' => 'Address deleted successfully']);
    }
}
