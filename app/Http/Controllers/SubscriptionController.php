<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\SubscriptionType;
use App\Models\Subscription;
use App\Models\Plan;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Auth;


class SubscriptionController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getSubscriptions(){
        try{
            $subscriptions= Subscription::latest()->get();
            return $this->successResponse($subscriptions);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getSubscription($id) {

        try{
            $subscription= Subscription::where('id', $id)->get();
            return $this->successResponse($subscription);
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
    public function addSubscription(Request $request)
    {
        try{

            $validator = $this->validateSubscription();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }


            $duplicateFound = Subscription::where('plan_id', $request->plan_id)
            ->where('user_id', $userId)->first();

            if($duplicateFound){
                return $this->errorResponse("You are already susbcribed to this plan", 422);
            }

            $subscription = new Subscription();
            $status = Status::where('name', 'pending')->firstOrFail();

            $subscription->status_id = $status->id;

            //get plan details
            $plan=Plan::find($request->plan_id);

            $start= Carbon::now()->format('Y-m-d');
            $start_date = Carbon::createFromFormat('Y-m-d', $start)->endOfDay();

            $end = (int)$plan->duration;
            $end_date_now = Carbon::now()->addDays($end)->format('Y-m-d');
            $end_date = Carbon::createFromFormat('Y-m-d',  $end_date_now)->endOfDay();


            $subscription->start_date = $start_date;
            $subscription->end_date = $end_date;
            $subscription->description = "Plan subscription for ". $plan->name;
            $subscription->plan_id = $request->plan_id;
            $subscription->user_id = $userId;
            $subscription->save();


            //redirect to payment and record a transaction


            //end record a transaction.

            return $this->successResponse($subscription,"Saved successfully", 200);

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
    public function updateSubscription(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateSubscription();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            $subscription=Subscription::find($id);
            $status=Status::findOrFail($subscription->status_id);

            if($request->plan_id){
                $duplicateFound = Subscription::where('plan_id', $request->plan_id)
                ->where('user_id', $userId)->first();

                if($duplicateFound){
                    return $this->errorResponse("You are already subscribed to this plan", 422);
                }

                $subscription->plan_id = $request->plan_id;
            }
            //check if transaction_id already exists.if it does, you cannot upgrade until subscription expires

            
            $subscription->save();
            //send notification to user

            //end notification
            return $this->successResponse($subscription,"Subscription Updated successfully", 200);

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
    public function deleteSubscription($id)
    {
        try{

            Subscription::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateSubscription(){
        return Validator::make(request()->all(), [
            'plan_id' => 'required|exists:plans,id'
        ]);
    }

}
