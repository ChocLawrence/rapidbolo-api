<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Status;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getNotifications(Request $request){

        try{

            $notification_query = Notification::with(['user','labels']);

            if($request->keyword){
                $notification_query->where('subject','LIKE','%'.$request->keyword.'%');
            }

            if($request->label_id){
                $notification_query->where('label_id',$request->label_id);
            }

            if($request->sender_id){
                $notification_query->where('sender_user_id', $request->sender_id);
            }

            if($request->receiver_id){
                $notification_query->where('receiver__user_id', $request->receiver_id);
            }

            if($request->sortBy && in_array($request->sortBy,['id','created_at'])){
                $sortBy = $request->sortBy;
            }else{
                $sortBy = 'id';
            }

            if($request->sortOrder && in_array($request->sortOrder,['asc','desc'])){
                $sortOrder = $request->sortOrder;
            }else{
                $sortOrder = 'desc';
            }

            if($request->page_size){
                $page_size = $request->page_size;
            }else{
                $page_size = 5;
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
                $end_date = $request->end_date;
            }else{
                $end_date = Carbon::now()->format('Y-m-d');
            }


            if($request->page){

                $start_date = Carbon::parse($start_date);
                $start_date->addHours(00)
                ->addMinutes(00);

                $end_date = Carbon::parse($end_date);
                $end_date->addHours(23)
                ->addMinutes(59);

                $notifications = $notification_query->orderBY($sortBy,$sortOrder)->whereBetween('created_at', array($start_date, $end_date))->paginate($page_size);
           
            }else{
                $notifications = $notification_query->orderBY($sortBy,$sortOrder)->get();
            }
  
            return $this->successResponse($notifications);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getNotification($id) {

        try{
            $notification= Notification::where('id', $id)->get();
            return $this->successResponse($notification);
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
    public function addNotification(Request $request)
    {
        try{
            $validator = $this->validateNotification();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $notification=new Notification();

            $status = Status::where('name', 'unread')->firstOrFail();
            $notification->summary= $request->summary;
            $notification->message= $request->message;
            $notification->sender_user_id= $request->sender_id;
            $notification->receiver_user_id= $request->receiver_id;
            $notification->label_id= $request->label_id;
            $notification->status_id= $status->id;
            $notification->url= $request->url;
            $notification->save();

            return $this->successResponse($notification,"Sent successfully", 200);

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
    public function updateNotificationStatus(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateNotificationStatus();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $notification=Notification::findOrFail($id);

            if($request->status_id){
                $status = Status::where('id', $request->status_id)->firstOrFail();
                $notification->status_id= $status->id;
            }
            
            $notification->save();

            return $this->successResponse($notification,"Updated successfully", 200);

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
    public function markAsRead(Request $request, $id)
    {

        try{

            $notification=Notification::findOrFail($id);
            $status = Status::where('name', 'read')->firstOrFail();
            $notification->status_id= $status->id;
            $notification->save();

            return $this->successResponse($notification,"Updated successfully", 200);

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
    public function markAsUnRead(Request $request, $id)
    {

        try{

            $notification=Notification::findOrFail($id);
            $status = Status::where('name', 'unread')->firstOrFail();
            $notification->status_id= $status->id;
            $notification->save();

            return $this->successResponse($notification,"Updated successfully", 200);

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
    public function deleteNotification($id)
    {
        try{

            Notification::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateNotification(){
        return Validator::make(request()->all(), [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'label_id' => 'nullable|exists:labels,id',
            'summary' => 'required|string|max:50', 
            'message' => 'required|string|max:1000', 
            'url' => 'nullable|string|max:100'
        ]);
    }

    public function validateNotificationStatus(){
        return Validator::make(request()->all(), [
            'status_id' => 'nullable|exists:statuses,id',
        ]);
    }
}
