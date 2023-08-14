<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use App\Models\Status;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;

class UserActivityController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserActivities(Request $request){

        try{
            $user_acts_query = UserActivity::with(['user','demand']);

            if($request->keyword){
                $user_acts_query->where('report','LIKE','%'.$request->keyword.'%');
            }

            if($request->user_id){
                $user_acts_query->where('user_id',$request->user_id);
            }

            if($request->sortBy && in_array($request->sortBy,['id','created_at'])){
                $sortBy = $request->sortBy;
            }else{
                $sortBy = 'created_at';
            }

            if($request->sortOrder && in_array($request->sortOrder,['asc','desc'])){
                $sortOrder = $request->sortOrder;
            }else{
                $sortOrder = 'desc';
            }

           
            $activities = $user_acts_query->orderBY($sortBy,$sortOrder)->get();
  
            return $this->successResponse($activities);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getUserActivity($id) {

        try{
            $activity= UserActivity::where('id', $id)->get();
            return $this->successResponse($activity);
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
    public function addUserActivity(Request $request)
    {
        try{
            
            $validator = $this->validateUserActivity();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $id = Auth::id();
            }

            $activity=new UserActivity();

            $activity->report= $request->report;

            if($request->state== "login" || $request->state== "reconnect"){
                $activity->online =  true;
            }else{
                //logout or lost connection
                $activity->online =  false;
            }

            $activity->state= $request->state;
            $activity->user_id= $request->user_id;

            if($request->longitude){
                $activity->longitude= $request->longitude;
            }
          
            if($request->latitude){
                $activity->latitude= $request->latitude;
            }
          
            $activity->save();

            return $this->successResponse($activity,"Saved successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }


    public function validateUserActivity(){
        return Validator::make(request()->all(), [
            'report' => 'required|string|max:100',
            'state' => 'required|in:login,logout,reconnect,lostcon', 
            'longitude' => 'nullable|string|max:100',
            'latitude' => 'nullable|string|max:100',
        ]);
    }
}
