<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "is_primary" => $this->is_primary,
            "surname" => $this->surname,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "address" => $this->address,
            "street_name" => $this->street_name,
            "street_number" => $this->street_number,
            "lat" => $this->lat,
            "lon" => $this->lon,
            "address2" => $this->address2,
            "country" => $this->country,
            "city" => $this->city,
            "zip" => $this->zip,
            "phone" => $this->phone,
            "phone_country_code" => $this->phone_country_code,
        ];
    }
}
