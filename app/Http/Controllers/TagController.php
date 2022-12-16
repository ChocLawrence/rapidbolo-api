<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class TagController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getTags(){
        try{
            $tags= Tag::latest()->get();
            return $this->successResponse($tags);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getTag($id) {

        try{
            $tag= Tag::where('id', $id)->get();
            return $this->successResponse($tag);
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
    public function addTag(Request $request)
    {
        try{
            $validator = $this->validateTag();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $tag=new Tag();
            $tag->name= $request->name;
            $tag->slug= Str::slug($request->name);
            $tag->save();

            return $this->successResponse($tag,"Saved successfully", 200);

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
    public function updateTag(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateTag();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $tag=Tag::findOrFail($id);
        
            $tag->name=$request->name;  
            $tag->slug=Str::slug($request->name);
            $tag->save();

            return $this->successResponse($tag,"Updated successfully", 200);

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
    public function deleteTag($id)
    {
        try{

            Tag::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateTag(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:100'
        ]);
    }
}
