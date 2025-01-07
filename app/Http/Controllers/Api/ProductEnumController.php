<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\GemStoneFacting;

class ProductEnumController extends Controller
{
    public function getFaceting()
    {
        $faceting = array_map(
            fn($case) => ['key' => $case->name, 'value' => $case->value],
            GemStoneFacting::cases()
        );

        if (!empty($faceting)) {
            return response()->json(['status' => true, 'message' => $faceting], 200);
        } else {
            return response()->json(['status' => false, 'message' => "Can't find any faceting"], 404);
        }
    }
}
