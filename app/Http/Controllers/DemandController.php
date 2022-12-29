<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Demand;
use App\Models\User;
use App\Models\Status;
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


class DemandController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDemands(){

        try{
            $demands= Demand::latest()->get();
            return $this->successResponse($demands);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getDemand($id) {

        try{
            $demand= Demand::where('id', $id)->firstOrFail();
            return $this->successResponse($demand);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Demand  $demand
     * @return \Illuminate\Http\Response
     */
    public function addDemand(Request $request)
    {

        try{

            $validator = $this->validateDemand();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }


            //check for duplicate requests

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
    

            if (Auth::check())
            {
                $id = Auth::id();
            }


            $status = Status::where('name', 'pending')->firstOrFail();
            $demand= new Demand();

            //check if service provider is same as service requester

            $service = Service::where('id', $request->service_id)->firstOrFail();
            
            if($service->user_id == $id){
              return $this->errorResponse('You cannot request a service from one you provide', 422);
            }


            //end check

            $demand->status_id = $status->id; 
            $demand->address= $request->address;
            $demand->service_id = $request->service_id;
            $demand->description= $request->description;
            $demand->longitude= $request->longitude;
            $demand->latitude= $request->latitude;
            $demand->user_id = $id;
            $demand->images=$imagesName;
            $demand->save();

            return $this->successResponse($demand,"Saved successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Demand  $demand
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateDemand(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateUpdateDemand();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            // get form image
            $images = $request->file('images');
            $demand = Demand::findOrFail($id);

            //check for duplicate requests

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

                $demand->images = $imagesName;
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            if($userId != $demand->user_id){
                return $this->errorResponse("Unauthorized action.", 422);
            }

            //check if service provider is same as service requester

            if($request->service_id){
                $service = Service::where('id', $request->service_id)->firstOrFail();
        
                if($service->user_id == $id){
                   return $this->errorResponse('You cannot request a service from one you provide', 422);
                }
    
                $demand->service_id= $request->service_id;
            }
          
            //end check


            if($request->address){
              $demand->address= $request->address;
            }

            if($request->description){
              $demand->description= $request->description;
            }

            if($request->longitude){
              $demand->longitude= $request->longitude;
            }

            if($request->latitude){
              $demand->latitude= $request->latitude;
            }
           
            $demand->save();

            return $this->successResponse($demand);
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
    public function deleteDemand($id)
    {
        try{
            $demand = Demand::find($id);
            $demand->delete();

            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function validateDemand(){
        return Validator::make(request()->all(), [
           'service_id' => 'required|exists:services,id',
           'address'=>'required|string|max:100',
           'image'=>'nullable|mimes:jpeg,bmp,png,jpg',
           'description'=>'required|max:200',
           'longitude'=>'nullable|string|max:20',
           'latitude'=>'nullable|string|max:20',
        ]);
    }

    public function validateUpdateDemand(){
        return Validator::make(request()->all(), [
           'service_id' => 'nullable|exists:services,id',
           'address'=>'nullable|string|max:100',
           'image'=>'nullable|mimes:jpeg,bmp,png,jpg',
           'description'=>'nullable|max:200',
           'longitude'=>'nullable|string|max:20',
           'latitude'=>'nullable|string|max:20',
        ]);
    }
}
