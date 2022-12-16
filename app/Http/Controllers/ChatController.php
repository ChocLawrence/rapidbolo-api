<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Request;
use App\Models\Service;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Auth;


class ChatController extends Controller
{
    //
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getChats(){

        try{
            $chats= Chat::latest()->get();
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
            $chat= Chat::where('id', $id)->firstOrFail();
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

          
            return $this->successResponse($chat,"Saved successfully", 200);

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
    public function updateChat(Request $request, $id)
    {

        try{

            return $this->successResponse('');
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
            

            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function validateChat(){
        return Validator::make(request()->all(), [
           'name'=>'required|unique:chats',
           'image'=>'required|mimes:jpeg,bmp,png,jpg'
        ]);
    }
}
