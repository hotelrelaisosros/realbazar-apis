<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Enums\MetalKarat;
use App\Enums\BandWidth;
use App\Enums\SettingHeight;
use App\Enums\RingSize;
use App\Enums\ProngStyle;
use App\Enums\GemStoneFacting;


class RingEnumController extends Controller
{
    public function getMetalKarats(): JsonResponse
    {
        return response()->json(MetalKarat::cases());
    }

    public function getBandWidths(): JsonResponse
    {
        return response()->json(BandWidth::cases());
    }

    public function getSettingHeights(): JsonResponse
    {
        return response()->json(SettingHeight::cases());
    }

    public function getRingSizes(): JsonResponse
    {
        return response()->json(RingSize::cases());
    }

    public function getProngStyles(): JsonResponse
    {
        return response()->json(ProngStyle::cases());
    }
    public function getGetStoneFacting(): JsonResponse
    {
        return response()->json(GemStoneFacting::cases());
    }

    public function getAllEnums(): JsonResponse
    {
        return response()->json([
            'metal_karat' => MetalKarat::cases(),
            'band_width' => BandWidth::cases(),
            'setting_height' => SettingHeight::cases(),
            'ring_size' => RingSize::cases(),
            'prong_style' => ProngStyle::cases(),
        ]);
    }
}
