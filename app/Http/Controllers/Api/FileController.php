<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use App\Helpers\ImageHelper;


class FileController extends Controller
{
    public function serveFile($filename)
    {
        // Prepend 'storage/' to the filename
        $filePath = 'storage/' . $filename;

        // Construct the full file path in the public folder

        $fullFilePath = public_path($filePath);


        // Check if the file exists
        $fileContent = file_get_contents($fullFilePath);

        // Serve the file content directly as plain output
        return response($fileContent);
        // If the file does not exist, return a 404 response
        // return response()->json(['status' => false, 'message' => 'File not found'], 500);
    }
}
