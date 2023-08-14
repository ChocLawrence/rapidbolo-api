<?php

namespace App\Http\Controllers;

use App\Models\VerificationType;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class VerificationTypeController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getVerificationTypes(){
        try{
            $verificationTypes= VerificationType::latest()->get();
            return $this->successResponse($verificationTypes);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getVerificationType($id) {

        try{
            $verificationType= VerificationType::where('id', $id)->get();
            return $this->successResponse($verificationType);
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
    public function addVerificationType(Request $request)
    {
        try{
            $validator = $this->validateVerificationType();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $verificationType=new VerificationType();
            $verificationType->name= $request->name;
            $verificationType->slug= Str::slug($request->name);
            $verificationType->save();

            return $this->successResponse($verificationType,"Saved successfully", 200);

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
    public function updateVerificationType(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateVerificationType();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $verificationType=VerificationType::findOrFail($id);
        
            $verificationType->name=$request->name;  
            $verificationType->slug=Str::slug($request->name);
            $verificationType->save();

            return $this->successResponse($verificationType,"Updated successfully", 200);

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
    public function deleteVerificationType($id)
    {
        try{

            VerificationType::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateVerificationType(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:100'
        ]);
    }
}
