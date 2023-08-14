<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Status;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getChats(Request $request){

        try{

            $chat_query = Chat::with(['user','labels']);

            if($request->keyword){
                $chat_query->where('subject','LIKE','%'.$request->keyword.'%');
            }

            if($request->label_id){
                $chat_query->where('label_id',$request->label_id);
            }

            if($request->sender_id){
                $chat_query->where('sender_user_id', $request->sender_id);
            }

            if($request->receiver_id){
                $chat_query->where('receiver_user_id', $request->receiver_id);
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

                $chats = $chat_query->orderBY($sortBy,$sortOrder)->whereBetween('created_at', array($start_date, $end_date))->paginate($page_size);
           
            }else{
                $chats = $chat_query->orderBY($sortBy,$sortOrder)->get();
            }
  
            return $this->successResponse($chats);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getChat($id) {

        try{
            $chat= Chat::where('id', $id)->get();
            return $this->successResponse($chat);
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
    public function addChat(Request $request)
    {
        try{
            $validator = $this->validateChat();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $chat=new Chat();

            $status = Status::where('name', 'unread')->firstOrFail();

             //get form image
             $images= $request->file('images');

             $length =  $request->length;
             if($length>0){
                 for ($i=0; $i < $length; $i++) { 
                     $img[$i] = $request->file('docs'.$i);
                     $path = $img[$i]->getRealPath();
                     $realImage = file_get_contents($path);
                     $image = base64_encode($realImage);
                     $images[] = $image;
                 }
                 $imagesName = json_encode($images);
             }else{
                 $imagesName = null;
             }

            $chat->message= $request->message;
            $chat->sender_user_id= $request->sender_id;
            $chat->receiver_user_id= $request->receiver_id;
            $chat->demand_id= $request->demand_id;
            $chat->status_id= $status->id;
            $chat->images= $imagesName;
            $chat->save();

            return $this->successResponse($chat,"Sent successfully", 200);

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

            $chat=Chat::findOrFail($id);
            $status = Status::where('name', 'read')->firstOrFail();
            $chat->status_id= $status->id;
            $chat->save();

            return $this->successResponse($chat,"Updated successfully", 200);

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

            $chat=Chat::findOrFail($id);
            $status = Status::where('name', 'unread')->firstOrFail();
            $chat->status_id= $status->id;
            $chat->save();

            return $this->successResponse($chat,"Updated successfully", 200);

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
    public function deleteChat($id)
    {
        try{

            Chat::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateChat(){
        return Validator::make(request()->all(), [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'images'=>'nullable|mimes:jpeg,bmp,png,jpg',
            'message' => 'required|string|max:1000', 
        ]);
    }

    public function validateChatStatus(){
        return Validator::make(request()->all(), [
            'status_id' => 'nullable|exists:statuses,id',
        ]);
    }
}
