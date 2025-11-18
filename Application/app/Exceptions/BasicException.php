<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BasicException extends Exception
{
    public function __construct(protected string $messageError = "", protected  int $statusCode = 0, protected  ?int $typeRequest = null) {}

    /**
     * Report the exception.
     */

    public function report(): void
    {
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $this->messageError
        ];
        return response()->json($response, $this->statusCode);
    }
}
