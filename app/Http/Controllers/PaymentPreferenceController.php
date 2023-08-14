<?php

namespace App\Http\Controllers;

use App\Models\PaymentPreference;
use App\Models\Status;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Auth;


class PaymentPreferenceController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPaymentPreferences(){

        try{
            $paymentPreferences= PaymentPreference::latest()->get();
            return $this->successResponse($paymentPreferences);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getPaymentPreference($id) {

        try{
            $paymentPreference= PaymentPreference::where('id', $id)->firstOrFail();
            return $this->successResponse($paymentPreference);
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
    public function addPaymentPreference(Request $request)
    {

        try{

            $validator = $this->validatePaymentPreference();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }
            
            $activeStatus = Status::where('name', 'active')->firstOrFail();
            $paymentPreference= new PaymentPreference();
            $paymentPreference->status_id= $request->status_id ? $request->status_id : $activeStatus->id;
            $paymentPreference->service_id= $request->service_id;
            $paymentPreference->payment_m_id= $request->payment_m_id;
            $paymentPreference->phone=$request->phone;
            $paymentPreference->save();

            return $this->successResponse($paymentPreference,"Saved successfully", 200);

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
    public function updatePaymentPreference(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateUpdatePaymentPreference();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }


            if($request->service_id){
              $paymentPreference->service_id= $request->service_id;
            }

            if($request->payment_m_id){
                $paymentPreference->payment_m_id= $request->payment_m_id;
            }

            if($request->status_id){
                $status = Status::where('id', $request->status_id)->firstOrFail();

                if(!$status){
                    return $this->errorResponse("Invalid status id", 404);  
                }

                $paymentPreference->status_id= $status->id;
            } 

            if($request->phone){
                $paymentPreference->phone= $request->phone;
            }

            $paymentPreference->save();

            return $this->successResponse($paymentPreference);
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
    public function deletePaymentPreference($id)
    {
        try{
            $paymentPreference = PaymentPreference::find($id);
            $paymentPreference->delete();

            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function validatePaymentPreference(){
        return Validator::make(request()->all(), [
           'service_id'=>'required|unique:services',
           'payment_m_id'=>'required|unique:payment_methods',
           'status_id'=>'required|unique:status',
           'phone'=>'required|unique|string',
        ]);
    }

    public function validateUpdatePaymentPreference(){
        return Validator::make(request()->all(), [
           'service_id'=>'required|unique:services',
           'payment_m_id'=>'nullable|unique:payment_methods',
           'status_id'=>'nullable|unique:status',
           'phone'=>'nullable|string',
        ]);
    }
}
