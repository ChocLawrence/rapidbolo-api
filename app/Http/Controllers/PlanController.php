<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\PlanType;
use App\Models\Plan;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Auth;


class PlanController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getPlans(){
        try{
            $plans= Plan::latest()->get();
            return $this->successResponse($plans);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getPlan($id) {

        try{
            $plan= Plan::where('id', $id)->get();
            return $this->successResponse($plan);
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
    public function addPlan(Request $request)
    {
        try{
            
            $validator = $this->validatePlan();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $id = Auth::id();
            }


            $duplicateFound = Plan::where('name', $request->name)->first();

            if($duplicateFound){
                return $this->errorResponse("Plan name already taken", 422);
            }

            $plan = new Plan();
            $status = Status::where('name', 'inactive')->firstOrFail();
            $plan->name = $request->name;
            $plan->duration = $request->duration;  
            $plan->description = $request->description;
            $plan->amount = $request->amount;
            $plan->status_id = $status->id;
            $plan->created_by = $id;
            $plan->save();

            return $this->successResponse($plan,"Saved successfully", 200);

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
    public function updatePlan(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validatePlanUpdate();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $plan=Plan::find($id);
            $status=Status::findOrFail($plan->status_id);

            if($request->name){
                $duplicateFound = Plan::where('name', $request->name)->first();

                if($duplicateFound){
                    return $this->errorResponse("Plan name already taken", 422);
                }

                $plan->name=$request->name;  
            }

            if($request->duration){
                $plan->duration=$request->duration;  
            }

            if($request->description){
                $plan->description=$request->description;  
            }

            if($request->amount){
                $plan->amount=$request->amount;  
            }

            if($request->status_id){
                $plan->status_id=$request->status_id;  
            }

            $plan->save();

            //send notification to user

            //end notification

            return $this->successResponse($plan,"Plan Updated successfully", 200);

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
    public function deletePlan($id)
    {
        try{

            Plan::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validatePlan(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:100',
            'duration' => 'required|numeric|min:31|max:365',
            'description' => 'required|string|max:200',
            'amount' => 'required|string|max:100',
            'status_id' => 'nullable|exists:statuses,id'
        ]);
    }

    public function validatePlanUpdate(){
        return Validator::make(request()->all(), [
            'name' => 'nullable|string|max:100',
            'duration' => 'nullable|numeric|min:31|max:365',
            'description' => 'nullable|string|max:200',
            'amount' => 'nullable|string|max:100',
            'status_id' => 'nullable|exists:statuses,id'
        ]);
    }
}
