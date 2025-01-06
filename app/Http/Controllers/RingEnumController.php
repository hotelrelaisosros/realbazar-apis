<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AccentStoneTypes;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\MetalKarat;
use App\Models\BandWidth;
use App\Models\BespokeCustomization;
use App\Models\BespokeCustomizationType;
use App\Models\BirthStone;
use App\Models\Gemshape;
use App\Models\GemStone;
use App\Models\SettingHeight;
use App\Models\RingSize;
use App\Models\ProngStyle;
use App\Models\GemStoneFaceting;
use App\Models\ProductEnum;

class RingEnumController extends Controller
{
    public function getGemShapes(): JsonResponse
    {
        $data = Gemshape::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Metal Karats not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getBandWidth(): JsonResponse
    {
        $data = BandWidth::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Band Widths not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getSettingHeights(): JsonResponse
    {
        $data = SettingHeight::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Setting Heights not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getRingSize(): JsonResponse
    {
        $data = RingSize::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Ring Sizes not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getProngStyles(): JsonResponse
    {
        $data = ProngStyle::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Prong Styles not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getGemStoneFaceting(): JsonResponse
    {
        $data = GemStoneFaceting::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Gem Stone Facting not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getBespokeCustomization(): JsonResponse
    {
        // Replace with your BespokeCustomization model
        $data = BespokeCustomization::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Bespoke Customization not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getBespokeCustomizationType(): JsonResponse
    {
        // Replace with your BespokeCustomizationType model
        $data = BespokeCustomizationType::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Bespoke Customization Type not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getBirthStone(): JsonResponse
    {
        // Replace with your BirthStone model
        $data = BirthStone::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Birth Stones not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }

    public function getAccentStoneType(): JsonResponse
    {
        // Replace with your BirthStone model
        $data = AccentStoneTypes::all();
        if ($data->isEmpty()) {
            return response()->json(['message' => 'Birth Stones not found'], 404);
        }
        return response()->json(['data' => $data], 200);
    }
    protected function formatImageUrl($imagePath)
    {
        if (!str_starts_with($imagePath, 'http')) {
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }


    public function bespoke_with_types()
    {
        $BespokeCustomizations = BespokeCustomization::with("bsp_type")->get();

        $BespokeCustomizations->each(function ($BespokeCustomization) {
            $BespokeCustomization->image = $this->formatImageUrl($BespokeCustomization->image); // Format image URL
        });


        return response()->json([

            'status' => true,
            'message' => 'No BespokeCustomizations found',
            'BespokeCustomizations' => $BespokeCustomizations,
        ], 200);
    }
    public function bespoke_one_with_types($id)
    {
        $BespokeCustomization = BespokeCustomization::with("bsp_type")->findOrFail($id);
        $BespokeCustomization->image = $this->formatImageUrl($BespokeCustomization->image);

        return response()->json($BespokeCustomization);
    }
}
