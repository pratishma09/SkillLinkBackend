<?php

namespace App\Responses;

class BaseResponse
{
    /**
     * Return a success response.
     *
     * @param mixed  $data
     * @param string $message
     * @param int    $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = [], $message = 'Request successful', $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int    $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message = 'Something went wrong', $statusCode = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $statusCode);
    }
}
