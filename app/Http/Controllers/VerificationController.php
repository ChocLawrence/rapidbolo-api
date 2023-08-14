<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\VerificationType;
use App\Models\Verification;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Auth;


class VerificationController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getVerifications(){
        try{
            $verifications= Verification::latest()->get();
            return $this->successResponse($verifications);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getVerification($id) {

        try{
            $verification= Verification::where('id', $id)->get();
            return $this->successResponse($verification);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addVerification(Request $request)
    {
        try{
            $validator = $this->validateVerification();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            // get form image
            $attachment = $request->file('attachment');
            if (isset($attachment))
            {
                $path = $attachment->getRealPath();
                $realAttachment= file_get_contents($path);
                $attachmentName = base64_encode($realAttachment);

            } else {
                $attachmentName = null;
            }

            if (Auth::check())
            {
                $id = Auth::id();
            }


            $duplicateFound = Verification::where('user_id', $id)
            ->where('verification_type_id', $request->verification_type_id)->first();

            if($duplicateFound){
                return $this->errorResponse("You have already submitted for this type.", 422);
            }

            $verification = new Verification();
            $status = Status::where('name', 'pending')->firstOrFail();
            $verification->verification_type_id = $request->verification_type_id;
            $verification->attachment = $attachmentName;
            $verification->status_id = $status->id;
            $verification->user_id = $id;
            $verification->save();

            return $this->successResponse($verification,"Saved successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateVerification(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateUpdateVerification();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }
            $verification=Verification::findOrFail($id);
            $approvedStatus = Status::where('name', 'approved')->firstOrFail();

            if($userId != $verification->user_id){
                return $this->errorResponse("Unauthorized", 404);  
            }


            if($verification->status_id ==  $approvedStatus->status_id){
               return $this->errorResponse("Already approved, cannot update", 404);  
            }

            // get form image
            $attachment = $request->file('attachment');
            if (isset($attachment))
            {
                $path = $attachment->getRealPath();
                $realAttachment= file_get_contents($path);
                $attachmentName = base64_encode($realAttachment);

            } else {
                $attachmentName = null;
            }

        
            if($attachmentName){
              $verification->attachment = $attachmentName;
            }
           
            if($request->verification_type_id){
              $verification->verification_type_id = $request->verification_type_id;
            }

            $verification->save();

            return $this->successResponse($status,"Updated successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
        
        
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function validateUserVerification(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateUserVerificationStatus();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $verification=Verification::find($id);
            $status=Status::findOrFail($verification->status_id);

            if($status->name == "approved"){
              return $this->errorResponse("Document already approved", 404); 
            }

            if($request->status_id){
                $verification->status_id=$request->status_id;  
            }

            $verification->save();

            //send notification to user

            //end notification

            return $this->successResponse($verification,"Status Updated successfully", 200);

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
    public function deleteVerification($id)
    {
        try{

            Verification::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateVerification(){
        return Validator::make(request()->all(), [
            'verification_type_id' => 'required|exists:verification_types,id',
            'attachment' => 'required|mimes:jpeg,png,jpg,pdf',
        ]);
    }

    public function validateUpdateVerification(){
        return Validator::make(request()->all(), [
            'verification_type_id' => 'nullable|exists:verification_types,id',
            'attachment' => 'required|mimes:jpeg,png,jpg,pdf',
        ]);
    }

    public function validateUserVerificationStatus(){
        return Validator::make(request()->all(), [
            'status_id' => 'nullable|exists:statuses,id',
        ]);
    }
}
