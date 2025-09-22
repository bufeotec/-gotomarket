<?php

namespace App\Helpers;

class ApiResponse{
    public static function success(string $message, array $data = [], int $code = 200){
        return response()->json([
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function error(string $message, array $errors = [], int $code = 400){
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
