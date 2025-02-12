<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function getAdress(Request $request)
    {
        $address = $request->user()->address;
        return response()->json(['address' => $address]);
    }
    public function getAllAddresses(Request $request)
    {
        $addresses = $request->user()->addresses;
        return response()->json(['addresses' => $addresses]);
    }
    public function editAddress(Request $request)
    {
        $addresses = $request->user()->addresses;
        return response()->json(['addresses' => $addresses]);
    }

    public function deleteAdrress($id)
    {
        $address = $request->user()->address;
        $address->delete();
        return response()->json(['message' => 'Address deleted successfully']);
    }
}
