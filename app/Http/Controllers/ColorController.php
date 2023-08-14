<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class ColorController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getColors(){
        try{
            $colors= Color::latest()->get();
            return $this->successResponse($colors);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getColor($id) {

        try{
            $color= Color::where('id', $id)->get();
            return $this->successResponse($color);
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
    public function addColor(Request $request)
    {
        try{
            $validator = $this->validateColor();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $color=new Color();
            $color->name= $request->name;
            $color->color_code= $request->color_code;
            $color->slug= Str::slug($request->name);
            $color->save();

            return $this->successResponse($color,"Saved successfully", 200);

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
    public function updateColor(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateColor();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $color=Color::findOrFail($id);
        
            $color->name=$request->name;  
            $color->color_code= $request->color_code;
            $color->slug=Str::slug($request->name);
            $color->save();

            return $this->successResponse($color,"Updated successfully", 200);

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
    public function deleteColor($id)
    {
        try{

            Color::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateColor(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:100',
            'color_code' => 'required|string|max:7'
        ]);
    }
}
