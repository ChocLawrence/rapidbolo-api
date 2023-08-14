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
use App\Models\StatusHistory;
use App\Models\UserActivity;
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

            //check if user has already logged in.
            
            $activity = UserActivity::where('user_id', $user->id)
                                    ->orderBy('created_at', 'desc')->first();


            if($activity->state == "login"){
                //user is already logged in
                return $this->errorResponse("User is already logged in", 404);
            }
            //end check if user has already logged in.

            //user activity 

            $userActivity= new UserActivity(); 
            $userActivity->report = "Login"; 
            $userActivity->state = "login";
            $userActivity->online = true; 
            $userActivity->user_id = $user->id; 
            
            if($request->longitude){
              $userActivity->longitude = $request->longitude;
            }

            if($request->latitude){
              $userActivity->latitude = $request->latitude;
            }

            $userActivity->save();

            //end user activity 

            //end check if user has already logged in

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
                'password' => Hash::make($request->password),
                'email' => $request->email
            ]);

             //user activity 

             $userActivity= new UserActivity(); 
             $userActivity->report = "Login from sign up"; 
             $userActivity->state = "login";
             $userActivity->online = true; 
             $userActivity->user_id = $user->id; 
             
             if($request->longitude){
               $userActivity->longitude = $request->longitude;
             }
 
             if($request->latitude){
               $userActivity->latitude = $request->latitude;
             }
 
             $userActivity->save();
 
             //end user activity 
    
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
    public function logout(Request $request)
    {
        try{

            $validator = $this->validateLogout();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }


            if (Auth::check())
            {
                $id = Auth::id();
            }

             //user activity 

             $userActivity= new UserActivity(); 
             $userActivity->report = "Logout"; 
             $userActivity->state = "logout";
             $userActivity->online = false; 
             $userActivity->user_id = $id; 
             
             if($request->longitude){
               $userActivity->longitude = $request->longitude;
             }
 
             if($request->latitude){
               $userActivity->latitude = $request->latitude;
             }
 
             $userActivity->save();
 
             //end user activity 

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

            //check if user is admin

            if(!$user->isAdmin()){

                if (Auth::check())
                {
                    $id = Auth::id();
                }

                if( $user->id != $id){
                    return $this->errorResponse("You are not authorized", 404);
                }
            }
            
            return $this->successResponse($user);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
        
    }

    /**
     * Post resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function banUser(Request $request)
    {
        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing data.Pass fields", 404);  
            }

            $validator = $this->validateUser();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $user = User::findOrFail($request->user_id);
            $userStatus = Status::where('id', $user->status_id)->firstOrFail();

            if($userStatus->name == 'banned'){
                //already banned
                return $this->errorResponse("User already banned", 404);  
            }else{
                //update status to banned
                $bannedStatus = Status::where('name', 'banned')->firstOrFail();
                $user->status_id = $bannedStatus->id;
                $user->save();

                return $this->successResponse(null,"User Banned successfully", 200);
            }


        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }


    /**
     * Post resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activateUser(Request $request)
    {
        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing data.Pass fields", 404);  
            }

            $validator = $this->validateUser();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $user = User::findOrFail($request->user_id);
            $userStatus = Status::where('id', $user->status_id)->firstOrFail();

            if($userStatus->name == 'active'){
                //already active
                return $this->errorResponse("User already active", 404);  
            }else{
                //update status to active
                $activeStatus = Status::where('name', 'active')->firstOrFail();
                $user->status_id = $activeStatus->id;
                $user->marked_date = null;
                $user->marked_exp_date = null;
                $user->save();

                return $this->successResponse(null,"User Activated successfully", 200);
            }


        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

      /**
     * Post resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function markUserDeletion(Request $request)
    {
        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing data.Pass fields", 404);  
            }

            $validator = $this->validateUser();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $user = User::findOrFail($request->user_id);
            $userStatus = Status::where('id', $user->status_id)->firstOrFail();

            if($userStatus->name == 'marked'){
                //already marked
                return $this->errorResponse("User already marked for deletion", 404);  
            }else{
                //update status to marked for deletion

                $markedStatus = Status::where('name', 'mark')->firstOrFail();
                $user->status_id = $markedStatus->id;
                $user->save();


                $today= Carbon::now()->format('Y-m-d');
                $marked_date = Carbon::createFromFormat('Y-m-d', $today);

                $marked_date_plus_30 = Carbon::now()->addDays(30)->format('Y-m-d');
                $end_date = Carbon::createFromFormat('Y-m-d',  $marked_date_plus_30)->endOfDay();
             

                //status history

                $statusHistory= new StatusHistory(); 
                $statusHistory->status_id = $markedStatus->id; 
                $statusHistory->user_id = $user->id; 
                $statusHistory->note = "User marked to delete in 30 days.";
                $statusHistory->user_marked_date = $marked_date; 
                $statusHistory->user_marked_exp_date = $end_date;
                $statusHistory->save();

                // status history end

             
                //send notification to user to inform that after 30days, data will be deleted

                return $this->successResponse(null,"User Marked for deletion successfully", 200);
            }


        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
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
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
        ]);
    }


    public function validateLogout(){
        return Validator::make(request()->all(), [
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
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
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
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


    public function validateUser(){
        return Validator::make(request()->all(), [
            'user_id' => 'nullable|exists:users,id',
        ]);
    }
    

}
