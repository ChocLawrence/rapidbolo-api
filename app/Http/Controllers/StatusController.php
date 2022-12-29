<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class StatusController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getStatuses(){
        try{
            $statuses= Status::latest()->get();
            return $this->successResponse($statuses);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getStatus($id) {

        try{
            $status= Status::where('id', $id)->get();
            return $this->successResponse($status);
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
    public function addStatus(Request $request)
    {
        try{
            $validator = $this->validateStatus();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $status=new Status();
            $status->name= $request->name;
            $status->slug= Str::slug($request->name);
            $status->description= $request->description;
            $status->save();

            return $this->successResponse($status,"Saved successfully", 200);

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
    public function updateStatus(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateStatus();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $status=Status::findOrFail($id);
        
            if($request->name){
              $status->name=$request->name;  
              $status->slug=Str::slug($request->name);
            }
           
            if($request->description){
              $status->description = $request->description;
            }

            $status->save();

            return $this->successResponse($status,"Updated successfully", 200);

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
    public function deleteStatus($id)
    {
        try{

            Status::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateStatus(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:100'
        ]);
    }
}
