<?php

namespace App\Responses;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class SendEmailResponse implements Responsable
{
    public function toResponse($request)
    {
        // Return a valid HTTP response, such as a JSON response or a redirect
        return response()->json(['message' => 'Email verification sent.'], Response::HTTP_OK);
    }
}