<?php

namespace App\Http\Resources;

use App\Models\Gemshape;
use App\Models\BandWidth;
use App\Models\AccentStoneTypes;
use App\Models\ProngStyle;
use App\Models\SettingHeight;
use App\Models\RingSize;
use App\Models\BirthStone;
use App\Models\Clarity;
use App\Models\GemStone;
use App\Models\GemStoneColor;
use App\Models\MetalKerat;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $customizables = json_decode($this->customizables);

        return [
            'product_id'    => $this->product_id,
            'title'         => $this->products->title,
            'description'   => $this->products->desc,
            'price'         => $this->product_price,
            'quantity'      => $this->qty,
            'subtotal'      => $this->subtotal,
            'size'      => $this->size,
            'initial_price' => $this->initial_price,

            'discount'      => $this->discount,

            'products' => $this->products ? [
                'id'   => $this->products->id,
                'title' => $this->variation->title . ' ' . $this->products->title,
                'price' => $this->products->price
            ] : null,
            'variation'     => $this->variation ? [
                'id'   => $this->variation->id,
                'size' => $this->variation->size,
                'price' => $this->variation->price
            ] : null,
            'product_images' => $this->product_images ?? [],
            'customizables'  => array_filter(
                [
                    'metal_kerat' => isset($customizables->metal_kerat) ? MetalKerat::find($customizables->metal_kerat) : null,
                    'clarity' => isset($customizables->clarity) ? Clarity::find($customizables->clarity) : null,
                    'gem_shape'                => isset($customizables->gem_shape_id) ? Gemshape::find($customizables->gem_shape_id) : null,
                    'band_width'               => isset($customizables->band_width_id) ? BandWidth::find($customizables->band_width_id) : null,
                    'accent_stone_type'        => isset($customizables->accent_stone_type_id) ? AccentStoneTypes::find($customizables->accent_stone_type_id) : null,
                    'setting_height'           => isset($customizables->setting_height_id) ? SettingHeight::find($customizables->setting_height_id) : null,
                    'prong_style'              => isset($customizables->prong_style_id) ? ProngStyle::find($customizables->prong_style_id) : null,
                    'ring_size'                => isset($customizables->ring_size_id) ? RingSize::find($customizables->ring_size_id) : null,
                    'bespoke_customization_type' => $bespoke ?? null,
                    'birth_stone'              => isset($customizables->birth_stone_id) ? BirthStone::find($customizables->birth_stone_id) : null,
                    'gem_stone'                => isset($customizables->gem_stone_id) ? GemStone::find($customizables->gem_stone_id) : null,
                    'gem_stone_color'          => isset($customizables->gem_stone_color_id) ? GemStoneColor::find($customizables->gem_stone_color_id) : null,
                    'engraved_text'            => $customizables->engraved_text ?? null,
                    'metal_type_karat'         => $customizables->metal_type_karat ?? null,
                ],
                fn($value) => !is_null($value)
            ),
        ];
    }
}
