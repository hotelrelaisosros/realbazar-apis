<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class ImageHelper
{
    public static function formatImageUrl($imagePath)
    {
        $imagePath = str_replace('\\', '', $imagePath);

        if (!str_starts_with($imagePath, 'https://')) {

            if (strpos($imagePath, '\\') !== false) {
                $imagePath = str_replace('\\', '', $imagePath);
            }
            return url('storage/' . $imagePath);
        }

        return $imagePath;
    }

    public function formatProductImages($all_image)
    {
        return $all_image->map(function ($image) {
            $image->image = self::formatImageUrl($image->image);
            $image->small_image = self::formatImageUrl($image->small_image);

            if (!empty($image->image_collection)) {
                $imageCollection = json_decode($image->image_collection, true);
                if (is_array($imageCollection)) {
                    $image->image_collection = array_map([self::class, 'formatImageUrl'], $imageCollection);
                }
            }

            return $image;
        });
    }
    public static function formatImageCollection($imageCollection)
    {
        if (!empty($imageCollection)) {
            // Decode the JSON-encoded image_collection
            $decodedCollection = json_decode($imageCollection, true);

            if (is_array($decodedCollection)) {
                // Format each image URL in the collection
                return array_map([self::class, 'formatImageUrl'], $decodedCollection);
            }
        }

        return [];
    }


    public function format_product($all_images)
    {
        if (is_null($all_images) || !is_array($all_images)) {
            // If $all_images is null or not an array, return it as-is
            return $all_images;
        }

        return array_map(function ($image) {
            // Check if the image has necessary keys before accessing them
            if (isset($image['image'])) {
                $image['image'] = self::formatImageUrl($image['image']);
            }

            if (isset($image['small_image'])) {
                $image['small_image'] = self::formatImageUrl($image['small_image']);
            }

            if (!empty($image['image_collection'])) {
                $imageCollection = json_decode($image['image_collection'], true);
                if (is_array($imageCollection)) {
                    $image['image_collection'] = array_map([self::class, 'formatImageUrl'], $imageCollection);
                }
            }

            return $image;
        }, $all_images);
    }


    // public  function hideImage($imagePath)
    // {
    //     if (!str_starts_with($imagePath, 'https://')) {
    //         // Return the "serve-file" URL with the relative file path
    //         return url("serve-file/{$imagePath}");
    //     } else {
    //         $resolvedPath = $imagePath; // If already a full URL, use as is
    //         return $imagePath;
    //     }
    // }

    public function formatProductImages2($all_image)
    {
        return $all_image->map(function ($image) {
            $image->image = self::formatImageUrl($image->image);
            $image->small_image = self::formatImageUrl($image->small_image);

            if (!empty($image->image_collection)) {
                $imageCollection = json_decode($image->image_collection, true);
                if (is_array($imageCollection)) {
                    $image->image_collection = array_map([self::class, 'formatImageUrl'], $imageCollection);
                }
            }

            return $image;
        });
    }
}
