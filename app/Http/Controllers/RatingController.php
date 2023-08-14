<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\User;
use App\Models\Service;
use App\Models\Demand;
use App\Models\Status;
use Auth;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;


class RatingController extends Controller
{
    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getRatings(){
        try{
            $ratings= Rating::latest()->get();
            return $this->successResponse($ratings);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getRating($id) {

        try{
            $rating= Rating::where('id', $id)->get();
            return $this->successResponse($rating);
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
    public function addRating(Request $request)
    {
        try{
            $validator = $this->validateRating();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $id = Auth::id();
            }

            $rating=new Rating();
            $rating->value= $request->value;
            $rating->comment= $request->comment;
            $rating->demand_id= $request->demand_id;
            $rating->user_id = $id;
            $rating->save();

            return $this->successResponse($rating,"Saved successfully", 200);

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
    public function deleteRating($id)
    {
        try{

            Rating::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateRating(){
        return Validator::make(request()->all(), [
            'value'  => 'required|in:1,2,3,4,5', 
            'comment' => 'required|string|max:300',
            'demand_id' => 'required|exists:demands,id'
        ]);
    }

}
