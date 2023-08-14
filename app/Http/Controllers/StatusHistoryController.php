<?php

namespace App\Http\Controllers;

use App\Models\StatusHistory;
use App\Models\Status;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class StatusHistoryController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getStatusHistories(Request $request){

        try{
            $status_h_query = StatusHistory::with(['user','demand']);

            if($request->keyword){
                $status_h_query->where('note','LIKE','%'.$request->keyword.'%');
            }

            if($request->status_id){
                $status_h_query->whereHas('status',function($query) use($request){
                    $query->where('slug',$request->status);
                });
            }

            if($request->demand_id){
                $status_h_query->where('demand_id',$request->demand_id);
            }

            if($request->user_id){
                $status_h_query->where('user_id',$request->user_id);
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

           
            $histories = $status_h_query->orderBY($sortBy,$sortOrder)->get();
  
            return $this->successResponse($histories);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getStatusHistory($id) {

        try{
            $status= StatusHistory::where('id', $id)->get();
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
    public function addStatusHistory(Request $request)
    {
        try{
            
            $validator = $this->validateStatusHistory();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            $status=new StatusHistory();
            $status->note= $request->note;
            $status->status_id= $request->status_id;

            if($request->demand_id){
                $status->demand_id= $request->demand_id;
            }
          
            if($request->user_id){
                $status->user_id= $request->user_id;
            }
          
            $status->save();

            return $this->successResponse($status,"Saved successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }


    /** UPDATE AND DELETE OF STATUS HISTORY SHOULD NEVER BE CALLED AS STATUS HISTORY 
     * SHOULD ALWAYS BE PRESENT FOR GUIDANCE AND AN ACCURATE AUDIT */
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatusHistory(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateStatusHistory();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $status=StatusHistory::findOrFail($id);
        
            if($request->note){
              $status->note=$request->note;  
            }
           
            if($request->status_id){
              $status->status_id = $request->status_id;
            }

            if($request->demand_id){
              $status->demand_id = $request->demand_id;
            }

            if($request->user_id){
              $status->user_id = $request->user_id;
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
    public function deleteStatusHistory($id)
    {
        try{

            StatusHistory::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateStatusHistory(){
        return Validator::make(request()->all(), [
            'note' => 'required|string|max:100',
            'status_id' => 'required|exists:statuses,id',
            'demand_id' => 'nullable|exists:demands,id',
            'user_id' => 'nullable|exists:users,id',
        ]);
    }
}
