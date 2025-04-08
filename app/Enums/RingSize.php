<?php


namespace App\Enums;

class RingSize
{
    public const sizes = [];

    // Static initializer
    public static function getSizes(): array
    {
        return range(41, 63);
    }
}
