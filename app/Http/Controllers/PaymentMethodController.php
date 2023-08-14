<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Status;
use Auth;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;


class PaymentMethodController extends Controller
{
    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getPaymentMethods(){
        try{
            $paymentMethods= PaymentMethod::latest()->get();
            return $this->successResponse($paymentMethods);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getPaymentMethod($id) {

        try{
            $paymentMethod= PaymentMethod::where('id', $id)->get();
            return $this->successResponse($paymentMethod);
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
    public function addPaymentMethod(Request $request)
    {
        try{
            $validator = $this->validatePaymentMethod();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            //check if name is duplicate

            $nameFound = PaymentMethod::where('name', $request->name)->first();
            
            if($nameFound){
              return $this->errorResponse('Name already taken', 422);
            }

            //end check

            if (Auth::check())
            {
                $id = Auth::id();
            }

            $paymentMethod=new PaymentMethod();
            $paymentMethod->name= $request->name;
            $paymentMethod->slug= Str::slug($request->name);
            $paymentMethod->description= $request->description;

            $status = Status::where('name', 'active')->firstOrFail();
            $paymentMethod->status_id = $status->id; 

            $paymentMethod->created_by = $id;
            $paymentMethod->save();

            return $this->successResponse($paymentMethod,"Saved successfully", 200);

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
    public function updatePaymentMethod(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validatePaymentMethodUpdate();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $paymentMethod=PaymentMethod::findOrFail($id);
        

            if($request->name){


                $nameFound = PaymentMethod::where('slug', Str::slug($request->name))->first();
                
                if($nameFound){
                return $this->errorResponse('name already taken', 422);
                }


                $paymentMethod->name=$request->name;  
                $paymentMethod->slug=Str::slug($request->name);
            }
          
            if($request->description){
                $paymentMethod->description=$request->description;  
            }

            if($request->status_id){
                $paymentMethod->status_id = $request->status_id; 
            }


            $paymentMethod->save();

            return $this->successResponse($paymentMethod,"Updated successfully", 200);

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
    public function deletePaymentMethod($id)
    {
        try{

            PaymentMethod::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validatePaymentMethod(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:300'
        ]);
    }

    public function validatePaymentMethodUpdate(){
        return Validator::make(request()->all(), [
            'name' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:300',
            'status_id' => 'nullable|exists:statuses,id'
        ]);
    }

}
