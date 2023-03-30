<?php


namespace App\Trait;


use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait HelperTrait
{
    /**
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function onSuccess(string $message = 'success', int $code = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * @param array|string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function onError(string $message = '', int $code = Response::HTTP_FORBIDDEN): JsonResponse
    {
        throw new HttpResponseException(response()->json([
            'errors' => ['text' => $message],
            'status' => true
        ], $code));
    }
}
