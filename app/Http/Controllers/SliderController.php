<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Carbon\Carbon;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\UploadedFile;
use App\Models\Tag;
use Illuminate\Support\Str;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use DB;


class SliderController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSliders(Request $request){

        try{
            $slider_query = Slider::with(['user']);

            if($request->keyword){
                $slider_query->where('name','LIKE','%'.$request->keyword.'%');
            }

            if($request->user_id){
                $slider_query->where('created_by',$request->created_by);
            }

            if($request->status){
                $slider_query->where('status',$request->status);
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
                $page_size = 10;
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

                $sliders = $slider_query->orderBY($sortBy,$sortOrder)->whereBetween('created_at', array($start_date, $end_date))->paginate($page_size);
           
            }else{
                $sliders = $slider_query->orderBY($sortBy,$sortOrder)->get();
            }


            return $this->successResponse($sliders);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getSlider($id) {

        try{
            $slider= Slider::where('id', $id)->firstOrFail();
            return $this->successResponse($slider);
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
    public function addSlider(Request $request)
    {

        try{

            $validator = $this->validateSlider();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            $image = $request->file('image');

    
            if(isset($image))
            {
                $path = $image->getRealPath();
                $realImage = file_get_contents($path);
                $imageName = base64_encode($realImage);
            } else {
                $imageName = null;
            }
    

            if (Auth::check())
            {
                $id = Auth::id();
            }
    
            $slider = new Slider();
            $slider->name = $request->name;
            $slider->title = $request->title;
            $slider->description = $request->description;
            $slider->status = $request->status ? $request->status : "inactive";
            $slider->created_by = $id;
            $slider->image = $imageName;
            $slider->save();

            return $this->successResponse($slider,"Slider added successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Slider  $slider
     * @return \Illuminate\Http\Response
     */
    public function updateSlider(Request $request, $id)
    {
        try{

            $slider= Slider::findOrFail($id);

            $validator = $this->validateUpdateSlider();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            $image = $request->file('image');
           
    
            if(isset($image))
            {
                $path = $image->getRealPath();
                $realImage = file_get_contents($path);
                $imageName = base64_encode($realImage);
    
            }else {
                $imageName = $slider->image;
            }


            if (Auth::check())
            {
                $userId = Auth::id();
            }
    
            $slider->name = $request->name;
            $slider->title = $request->title;
            $slider->description = $request->description;
            $slider->status = $request->status;
            $slider->created_by = $userId;
            $slider->image = $imageName;
            $slider->save();
    
            return $this->successResponse($slider,"Slider Updated successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
          
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Slider  $slider
     * @return \Illuminate\Http\Response
     */
    public function deleteSlider(Slider $id)
    {

        try{

            $slider = Slider::find($id)->first();
            $slider->delete();

            return $this->successResponse(null,"Slider Deleted successfully", 200);
            

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }

    }

    public function validateSlider(){
        return Validator::make(request()->all(), [
            'name' => 'required|min:15',
            'title' => 'nullable|string|min:15',
            'description' => 'nullable|string|min:15',
            'image' => 'required|image',
            'status' => 'nullable|string',
        ]);
    }

    public function validateUpdateSlider(){
        return Validator::make(request()->all(), [
            'name' => 'required|min:15',
            'title' => 'required|string|min:15',
            'description' => 'required|string|min:15',
            'image' => 'nullable|image',
            'status' => 'nullable|string',
        ]);
    }


}
