<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionType;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\Status;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Auth;


class TransactionController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getTransactions(){
        
        try{
            $transaction_query = Transaction::with(['payment_methods','status']);

            if($request->keyword){
                $transaction_query->where('title','LIKE','%'.$request->keyword.'%');
            }

            if($request->transaction_type_id){
                $transaction_query->whereHas('transaction_type',function($query) use($request){
                    $query->where('name',$request->transaction_type_id);
                });
            }

            if($request->user_id){
                $transaction_query->where('user_id',$request->user_id);
            }

            if($request->sortBy && in_array($request->sortBy,['id','date'])){
                $sortBy = $request->sortBy;
            }else{
                $sortBy = 'date';
            }

            if($request->sortOrder && in_array($request->sortOrder,['asc','desc'])){
                $sortOrder = $request->sortOrder;
            }else{
                $sortOrder = 'desc';
            }

            if($request->page_size){
                $page_size = $request->page_size;
            }else{
                $page_size = 9;
            }

            if($request->start_date){
                $validator = $this->validateStartDate();
                if($validator->fails()){
                  return $this->errorResponse($validator->messages(), 422);
                }
                $start_date = $request->start_date;
            }else{
                $start_date =  Carbon::now()->subMonth(1)->format('Y-m-d');
            }

            if($request->end_date){
                $validator = $this->validateEndDate();
                if($validator->fails()){
                  return $this->errorResponse($validator->messages(), 422);
                }
                $end_date = Carbon::createFromFormat('Y-m-d',  $request->end_date)->endOfDay();
            }else{
                $end = Carbon::now()->format('Y-m-d');
                $end_date = Carbon::createFromFormat('Y-m-d',  $end)->endOfDay();
            }


            if($request->page){

                $start_date = Carbon::parse($start_date);
                $start_date->addHours(00)
                ->addMinutes(00);

                $end_date = Carbon::parse($end_date);
                $end_date->addHours(23)
                ->addMinutes(59);

                $transactions = $transaction_query->orderBY($sortBy,$sortOrder)->whereBetween('created_at', array($start_date, $end_date))->paginate($page_size);
           
            }else{
                $transactions = $transaction_query->orderBY($sortBy,$sortOrder)->get();
            }

            if($request->visibility == "0"){ 
               $transactions->makeHidden(['user_id'])->toArray();
            }
  
            return $this->successResponse($transactions);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getTransaction($id) {

        try{
            $transaction= Transaction::where('id', $id)->get();
            return $this->successResponse($transaction);
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
    public function addTransaction(Request $request)
    {
        try{

            $validator = $this->validateTransaction();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            $transaction=new Transaction();
            $transaction->payment_method_id= $request->payment_method_id;
            $transaction->country_id= $request->country_id;

            // $country =  Country::find($request->country_id);
            // $currency = $country->currency_name;

            $transactionTypeFound= TransactionType::find($request->transaction_type_id)->first();

            if($transactionTypeFound){
                if($transactionTypeFound->name == "subscription"){
                     if(!$request->subscription_id){
                        return $this->errorResponse("Subscription id is required for a subscription payment", 422);
                     }else{
                        $subscription = Subscription::find($request->subscription_id);
                        $planDetails = Plan::find($subscription->plan_id);
                        $description = "Subscription payment for plan: ".$planDetails->name;
                     }

                    //check if transaction amount equals subscription plan amount

                    if($planDetails->amount !== $request->amount){
                        return $this->errorResponse("Transaction amount not equal to plan amount", 422);
                    }

                }else if($transactionTypeFound->name == "demand"){
                    if($request->demand_id){
                        $transaction->demand_id= $request->demand_id;
                    }else{
                        return $this->errorResponse("Demand Id is mandatory for transaction type", 422); 
                    }
                }
            }else{
              return $this->errorResponse("Transaction type not found", 422);
            }

           
            //payment begins


            //payment ends

            $status = Status::where('name', 'approved')->firstOrFail();
            $transaction->transaction_type_id= $request->transaction_type_id;
            
            
            $transaction->description= $description;
            $transaction->status_id= $status->id;
            $transaction->amount= $request->amount;
            $transaction->user_id= $userId;
            $transaction->save();

            //update subscription entry with transaction id and status

            if($subscription->transaction_id){
                return $this->errorResponse("Transaction already exists for this.ABORT", 422);
            }else{
                $subscription->transaction_id = $transaction->id;
                $subscription->status_id = $status->id;
                $subscription->save();
            }

            //notify user 

            return $this->successResponse($transaction,"Saved successfully", 200);

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
    public function deleteTransaction($id)
    {
        try{

            Transaction::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateTransaction(){
        return Validator::make(request()->all(), [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'amount' => 'required|string',
            'demand_id' => 'nullable|exists:demands,id',
            'subscription_id' => 'nullable|exists:subscriptions,id',
            'description' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
        ]);
    }

}
