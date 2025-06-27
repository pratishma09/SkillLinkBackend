<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the incoming request to ensure the email is valid
        $request->validate(['email' => 'required|email']);

        // Send the password reset link
        $response = Password::sendResetLink($request->only('email'));

        // Check the response from the Password facade
        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Reset link sent to your email.'], 200);
        }

        return response()->json(['message' => 'Unable to send reset link.'], 400);
    }


    public function showResetForm(Request $request, $token = null)
{
    return view('auth.reset-password', [
        'token' => $token,
        'email' => $request->email
    ]);
}

public function reset(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email', // Ensure email is validated
        'password' => 'required|confirmed|min:8',
    ]);

    $response = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password),
            ])->save();
        }
    );

    if ($response == Password::PASSWORD_RESET) {
        // Redirect to frontend after successful reset
        return redirect('http://localhost:3000/login')->with('status', __($response));
    }

    return back()->withErrors(['email' => __($response)]);
}

}
