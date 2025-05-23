<?php

namespace App\Responses;

use Laravel\Fortify\Fortify;

class HasEmailResponse extends BaseResponse
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
        ? response()->json([
            'message' => 'Your email is already verified',
        ], 200)
        : redirect()->intended(Fortify::redirects('email-verification'));
    }
}