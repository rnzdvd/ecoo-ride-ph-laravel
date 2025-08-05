<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;

abstract class Controller
{
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
        ], 422);
    }
}
