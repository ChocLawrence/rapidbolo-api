<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Mail\SendMail;

class MailController extends Controller
{
    //

    use ApiResponser;
    
    public function sendMail(Request $request){
        try {
            $validator = $this->validateMail();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $details = [
            'title'=> $request->title,
            'body' => $request->body,
            'email' => $request->email,
            'subject' =>  $request->subject,
            ];

            $arrayEmails = [$details['email']];

            Mail::to($arrayEmails)->send(new SendMail($details));

            return $this->successResponse(null,"Email has been sent", 200);
        } catch(\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function validateMail(){
        return Validator::make(request()->all(), [
            'email' => 'required|string|email|max:255',
            'title' => 'required|string|min:6',
            'subject' => 'required|string|max:20',
            'body' => 'required|string|max:300',
        ]);
    }
}
