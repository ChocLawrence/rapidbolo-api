<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class LabelController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getLabels(){
        try{
            $labels= Label::latest()->get();
            return $this->successResponse($labels);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getLabel($id) {

        try{
            $label= Label::where('id', $id)->get();
            return $this->successResponse($label);
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
    public function addLabel(Request $request)
    {
        try{
            $validator = $this->validateLabel();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $label=new Label();
            $label->name= $request->name;
            $label->color_id= $request->color_id;
            $label->priority= $request->priority;
            $label->save();

            return $this->successResponse($label,"Saved successfully", 200);

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
    public function updateLabel(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $validator = $this->validateLabel();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $label=Label::findOrFail($id);
        
            $label->name=$request->name;  
            $label->color_id= $request->color_id;
            $label->priority= $request->priority;
            $label->save();

            return $this->successResponse($label,"Updated successfully", 200);

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
    public function deleteLabel($id)
    {
        try{

            Label::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateLabel(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:100',
            'color_id' => 'required|exists:colors,id',
            'priority' => 'nullable|integer|min:0|max:10',
        ]);
    }
}
