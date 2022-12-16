<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class SubscriberController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getSubscribers(){
        try{
            $subscribers= Subscriber::latest()->get();
            return $this->successResponse($subscribers);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getSubscriber($id) {

        try{
            $subscriber= Subscriber::where('id', $id)->get();
            return $this->successResponse($subscriber);
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
    public function addSubscriber(Request $request)
    {
        try{

            $validator = $this->validateSubscriber();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $emailFound= Subscriber::where('email',  $request->email)->first();

            if($emailFound){
                return $this->errorResponse("You have already subscribed", 422);
            }

            $subscriber=new Subscriber();
            $subscriber->email= $request->email;
            $subscriber->save();

            return $this->successResponse($subscriber,"Saved successfully", 200);

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
    public function updateSubscriber(Request $request, $id)
    {

        try{

            $validator = $this->validateSubscriber();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $subscriber=Subscriber::findOrFail($id);
        
            $subscriber->email=$request->email;  
            $subscriber->save();

            return $this->successResponse($subscriber,"Updated successfully", 200);

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
    public function deleteSubscriber($id)
    {
        try{

            Subscriber::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateSubscriber(){
        return Validator::make(request()->all(), [
            'email' => 'required|string|email',
        ]);
    }
}
