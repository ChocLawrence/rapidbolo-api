<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
    use ApiResponser;

    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->errorResponse("Email already verified", 422);
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->successResponse(null, "Verification link sent", 200);
    }

    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->errorResponse("Email already verified", 422);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->successResponse(null, "Email has been verified successfully", 200);
    }
}