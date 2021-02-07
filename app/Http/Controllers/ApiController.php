<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{

    // send the success and fail response
    public function sendResponse($status, $message, $data, $httpStatus)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $httpStatus);
    }
}
