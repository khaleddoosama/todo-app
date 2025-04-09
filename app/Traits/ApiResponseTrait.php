<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function apiResponse($data = null, $message = null, $status = null)
    {
        // If $data is a JsonResource or AnonymousResourceCollection, return it as a proper response
        if ($data instanceof \Illuminate\Http\Resources\Json\JsonResource) {
            return $data->additional([
                'message' => $message,
            ])->response()->setStatusCode($status ?? 200);
        }


        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $status ?? 400);
    }
}
