<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    use ApiResponser;

    public function successResponse($data, $code = 200)
    {
        return response()->json(['data' => $data], $code);
    }

    public function errorResponse($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    public function index()
    {
        // Example method to demonstrate functionality
        return $this->successResponse(['message' => 'API Controller is working']);
    }
}