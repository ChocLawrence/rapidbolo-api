<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password as RulesPassword;
use App\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Code;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use DB;
use Illuminate\Support\Facades\Validator;
use Auth;
use Hash;

class UserController extends Controller
{
    //
    use ApiResponser;

    public function root()
    {
       //root route
    }

    public function login(Request $request){

        try{

            $validator = $this->validateLogin();
            if($validator->fails()){
                return $this->errorResponse($validator->messages(), 422);
            }

            if(filter_var($request->username , FILTER_VALIDATE_EMAIL)){
                $user = User::where('email',$request->username)->first();
            }else{
                $user = User::where('username',$request->username)->first();
            }

            if(!$user || !Hash::check($request->password,$user->password)){
                return $this->errorResponse("Not a valid user", 404);
            }

            if (!$user->hasVerifiedEmail()) {
                return $this->errorResponse("Verify email before logging in.", 404);
            }

            $token = $user->createToken((string)$request->device_name)->plainTextToken;

            $response = [
                "user"=>$user,
                "token"=>$token
            ];

            return $this->successResponse($response,"Login successful", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

     //this method adds new users
     public function signup(Request $request)
     {

        try{
           
            $validator = $this->validateRegister();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }


            $user = User::create([
                'firstname' => $request->firstname,
                'middlename' => $request->middlename,
                'lastname' => $request->lastname,
                'username' => $request->username,
                'phone' => $request->phone,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'role_id' => '1',
                'status_id' => '2',
                'plan_id' => '1',
                'password' => Hash::make($request->password),
                'email' => $request->email
            ]);
    
            $response = [
               'token' => $user->createToken((string)$request->device_name)->plainTextToken
            ];

            $user->sendEmailVerificationNotification();
   
           return $this->successResponse($response, "Signup successful.Check mail for verification link", 201);
   
            
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }

    }

     // this method signs out users by removing tokens
    public function logout()
    {
        try{

            auth()->user()->tokens()->delete();
            $response = [
                'message' => 'Tokens Revoked'
            ];
            return $this->successResponse($response,"Logout Successful",200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
      
    }

    public function forgotPassword(Request $request)
    {

        $validator = $this->validateForgotPassword();
        if($validator->fails()){
           return $this->errorResponse($validator->messages(), 422);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            $response = [
                'status' => __($status)
            ];
            return $this->successResponse($response,"Reset link has been sent", 200);
        }

        return $this->errorResponse(trans($status), 422);
    }

    public function reset(Request $request)
    {
        $validator = $this->validateResetPassword();
        if($validator->fails()){
           return $this->errorResponse($validator->messages(), 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return $this->successResponse(null,"Password reset successfully", 200);
        }

        return $this->errorResponse(__($status),500);

    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getUsers(){
        try{
            $users= User::latest()->get();
            return $this->successResponse($users);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getUser($id) {

        try{
            $user= User::where('id', $id)->firstOrFail();
            return $this->successResponse($user);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
        
    }


     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteUser($id)
    {
        try{

            User::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function updateUser(Request $request)
    {
        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateProfile();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }


            $image = $request->file('image');
            $slug = Str::slug($request->firstname);
            $user = User::findOrFail(Auth::id());
            if (isset($image))
            {
                $path = $image->getRealPath();
                $realImage = file_get_contents($path);
                $imageName = base64_encode($realImage);
            } else {
                $imageName = $user->image;
            }

            if($request->firstname){
              $user->firstname = $request->firstname;
            }
           
            if($request->middlename){
              $user->middlename = $request->middlename;
            }

            if($request->lastname){
              $user->lastname = $request->lastname;
            }

            if($request->username){
              $user->username = $request->username;
            }
        
            if($request->gender){
              $user->gender = $request->gender;
            }
          
            if($request->dob){
              $user->dob = $request->dob;
            }

            if($request->bio){
              $user->bio = $request->bio;
            }

            if($request->address){
                $user->address = $request->address;
            }

            if($request->phone){
              $user->phone = $request->phone;
            }

            if($request->email){
              $user->email = $request->email;
            }

            if($request->longitude){
                $user->longitude = $request->longitude;
            }

            if($request->latitude){
                $user->latitude = $request->latitude;
            }

            if (isset($image)){
                $user->image = $imageName;
            }
           
            $user->save();
            return $this->successResponse($user,"Updated successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }

    }


    public function validateProfile(){
        return Validator::make(request()->all(), [
            'firstname' => 'string|min:2|max:50',
            'middlename' => 'string|min:2|max:50',
            'lastname' => 'string|min:2|max:50',
            'username' => 'string|min:6|max:15',
            'bio' => 'nullable|string|max:500',
            'phone' => 'string|min:7|max:20',
            'dob' => 'date_format:Y-m-d|before:today',
            'gender' => 'in:male,female', 
            'email' => 'email|max:255|unique:users,email,' .Auth::id()
        ]);
    }


    public function validateLogin(){
        return Validator::make(request()->all(), [
            'username' => 'required|string|max:40',
            'password' => 'required|string|min:6',
        ]);
    }

    public function validateRegister(){

        return Validator::make(request()->all(), [
            'firstname' => 'required|string|max:100',
            'middlename' => 'string|max:100',
            'lastname' => 'required|string|max:100',
            'username' => 'required|string|min:6|max:15',
            'phone' => 'string|min:7|max:20',
            'dob' => 'date_format:Y-m-d|before:today',
            'gender' => 'required|in:male,female', 
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);
    }

    public function validateForgotPassword(){
        return Validator::make(request()->all(), [
            'email' => 'required|string|email|max:255'
        ]);
    }

    public function validateResetPassword(){
        return Validator::make(request()->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', RulesPassword::defaults()]
        ]);
    }
    

}
