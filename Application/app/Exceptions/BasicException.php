<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class BasicException extends Exception
{
    public function __construct(protected string|array $messageError = "", protected  int $statusCode = 0, protected  ?int $typeRequest = null) {}

    /**
     * Report the exception.
     */

    public function report(): void {}

    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        $response = [
            "status" => false,
            "message" => $this->messageError,
            "authorization" => [
                "status" => "reproved"
            ]
        ];
        return response()->json($response, $this->statusCode);
    }
}
