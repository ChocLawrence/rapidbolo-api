<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Models\Status;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Auth;


class ServiceController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getServices(){

        try{
            $services= Service::latest()->get();
            return $this->successResponse($services);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getService($id) {

        try{
            $service= Service::where('id', $id)->firstOrFail();
            return $this->successResponse($service);
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
    public function addService(Request $request)
    {

        try{

            $validator = $this->validateService();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

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

            //check if user already has same service registered
            $duplicateFound = Service::where('user_id', $id)
                                    ->where('category_id', $request->category_id)
                                    ->first();

            if($duplicateFound){
               return $this->errorResponse("User is already providing this service.", 422);
            }               
            //if false, proceed to create service for user.


            $service= new Service();
            $service->address= $request->address;
            $service->category_id = $request->category_id;
            $service->description= $request->description;
            $service->longitude= $request->longitude;
            $service->latitude= $request->latitude;
            $service->user_id = $id;
            $service->images=$imagesName;
            $service->save();

            return $this->successResponse($service,"Saved successfully", 200);

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
    public function updateService(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateUpdateService();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            // get form image
            $images = $request->file('images');
            $service = Service::findOrFail($id);
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

                $service->images = $imagesName;
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            if($userId != $service->user_id){
                return $this->errorResponse("Unauthorized action.", 422);
            }


            if($request->address){
              $service->address= $request->address;
            }

            if($request->description){
              $service->description= $request->description;
            }

            if($request->longitude){
              $service->longitude= $request->longitude;
            }

            if($request->latitude){
              $service->latitude= $request->latitude;
            }

            if($request->category_id){

                //check if user already has same service registered
                $duplicateFound = Service::where('user_id', $id)
                            ->where('category_id', $request->category_id)
                            ->first();

                if($duplicateFound){
                       return $this->errorResponse("User is already providing this service.Change category.", 422);
                }               
                //if false, proceed to set field.

                $service->category_id= $request->category_id;
            }

           
            $service->save();

            return $this->successResponse($service);
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
    public function deleteService($id)
    {
        try{
            $service = Service::find($id);
            $service->delete();

            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function validateService(){
        return Validator::make(request()->all(), [
           'category_id' => 'required|exists:categories,id',
           'address'=>'required|string|max:100',
           'image'=>'nullable|mimes:jpeg,bmp,png,jpg',
           'description'=>'required|max:200',
           'longitude'=>'nullable|string|max:20',
           'latitude'=>'nullable|string|max:20',
        ]);
    }

    public function validateUpdateService(){
        return Validator::make(request()->all(), [
           'category_id' => 'nullable|exists:categories,id',
           'address'=>'nullable|string|max:100',
           'image'=>'nullable|mimes:jpeg,bmp,png,jpg',
           'description'=>'nullable|max:200',
           'longitude'=>'nullable|string|max:20',
           'latitude'=>'nullable|string|max:20',
        ]);
    }
}
