<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class FeedbackController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getFeedbacks(){

        try{
            $feedbacks= Feedback::latest()->get();
            return $this->successResponse($feedbacks);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getFeedback($id) {

        try{
            $feedback= Feedback::where('id', $id)->get();
            return $this->successResponse($feedback);
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
    public function addFeedback(Request $request)
    {
        try{
            $validator = $this->validateFeedback();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $feedback=new Feedback();
            $feedback->name= $request->name;
            $feedback->email= $request->email;
            $feedback->phone= $request->phone;
            $feedback->message= $request->message;
            $feedback->save();

            return $this->successResponse($feedback,"Saved successfully", 200);

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
    public function deleteFeedback($id)
    {
        try{

            Feedback::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateFeedback(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'message' => 'required|string|max:200',
        ]);
    }
}
