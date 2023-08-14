<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use App\Models\Demand;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Models\VerificationType;
use App\Models\Verification;
use App\Models\Notification;
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

            if (Auth::check())
            {
                $id = Auth::id();
            }

            //check if user has verified all active verification types
            $activeStatus = Status::where('name', 'active')->firstOrFail();
            $approvedStatus = Status::where('name', 'approved')->firstOrFail();

            $verificationTypes = VerificationType::where('status_id', $activeStatus->id)->get();
            $activeVerificationTypes = $verificationTypes->count();


            $verifications = Verification::where('status_id', $approvedStatus->id)
                                         ->where('user_id', $id)->get();
            $approvedUserVerifications = $verifications->count();

            if($activeVerificationTypes != $approvedUserVerifications){
                return $this->errorResponse("Please ensure your documents are approved", 422);
            }
            //end user check for verified verification types

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
    

            //check if user already has same service registered
            $duplicateFound = Service::where('user_id', $id)
                                    ->where('category_id', $request->category_id)
                                    ->first();

            if($duplicateFound){
               return $this->errorResponse("User is already providing this service.", 422);
            }               
            //if false, proceed to create service for user.

            if(!$request->status_id){
              $statusFound = Status::where('name', 'active')->firstOrFail();
              $status = $statusFound->id;
            }else{
              $status = $request->status_id;
            }
           

            $service= new Service();
            $service->address= $request->address;
            $service->category_id = $request->category_id;
            $service->description= $request->description;
            $service->longitude= $request->longitude;
            $service->latitude= $request->latitude;
            $service->status_id = $status;
            $service->user_id = $id;
            $service->images=$imagesName;
            $service->save();

            //status history

            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $status; 
            $statusHistory->service_id = $service->id; 
            $statusHistory->note = "Service created";
            $statusHistory->save();

            // status history end

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


            $acceptedStatus = Status::where('name', 'accepted')->firstOrFail();
            $progressStatus = Status::where('name', 'progress')->firstOrFail();
            $progressP1Status = Status::where('name', 'progress-p1')->firstOrFail();
            $progressP2Status = Status::where('name', 'progress-p2')->firstOrFail();
            $incompleteStatus = Status::where('name', 'incomplete')->firstOrFail();

            if($request->status_id){

                $status = Status::where('id', $request->status_id)->firstOrFail();

                if(!$status){
                    return $this->errorResponse("Invalid status id", 404);  
                }

                if($status->name = "inactive"){
                    //check if any demand is in progress to hinder service from being inactive.
                    $demands = Demand::where('service_id', $service->id)
                                    ->where(function($query) {
                                        $query->where('status_id', $acceptedStatus->id)
                                        ->orWhere('status_id', $progressStatus->id)
                                        ->orWhere('status_id', $progressP1Status->id)
                                        ->orWhere('status_id', $progressP2Status->id)
                                        ->orWhere('status_id', $incompleteStatus->id);
                                    })->get();

                    if($demands){
                        return $this->errorResponse("Service cannot be marked inactive since some demands are active", 404);  
                    }

                    $service->status_id= $status->id;

                    //status history

                    $statusHistory= new StatusHistory(); 
                    $statusHistory->status_id = $status->id; 
                    $statusHistory->service_id = $service->id; 
                    $statusHistory->note = "Service marked inactive";
                    $statusHistory->save();

                    // status history end


                }else{
                    return $this->errorResponse("Unauthorized", 404);  
                }

                
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
    public function suspendService($id)
    {
        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing field", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateSuspendService();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }


            $service = Service::find($id);

            //service can be suspended for some days and any pending demands cancelled.
            // once review complete/days over, it can be reinstated.

            $note  = $request->note ? $request->note : '';

            $suspendedStatus = Status::where('name', 'suspended')->firstOrFail();
            $service->status_id= $suspendedStatus->id;
            $service->save();

            //status history

            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $suspendedStatus->id; 
            $statusHistory->service_id = $service->id; 
            $statusHistory->note = "Service suspended for ".$service->days." days.>> ".$note ;
            $statusHistory->suspension_days = $request->days; 
            $suspensionExpiration = Carbon::now()->addDays((int)$request->days)->format('Y-m-d');
            $statusHistory->suspension_exp = $suspensionExpiration;
            $statusHistory->save();

            // status history end


            //send notification to service provider

            return $this->successResponse(null,"Service suspended successfully", 200);

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

            // if any demand exists relating to service, which is incomplete, 

            $demands = Demand::where('service_id', $service->id)
                                ->where(function($query) {
                                    $query->where('status_id', $acceptedStatus->id)
                                    ->orWhere('status_id', $progressStatus->id)
                                    ->orWhere('status_id', $progressP1Status->id)
                                    ->orWhere('status_id', $progressP2Status->id)
                                    ->orWhere('status_id', $incompleteStatus->id);
                                })->get();

            if($demands){

                return $this->errorResponse("Service cannot be deleted", 404);  

            }else if($request->confirmation == "valid2Q28A2TGLMG"){
                // after review, okay to delete
                $service->delete();

            }else{

                $deletedStatus = Status::where('name', 'deleted')->firstOrFail();
                $service->status_id= $deletedStatus->id;
                $service->save();

                //status history

                $statusHistory= new StatusHistory(); 
                $statusHistory->status_id = $deletedStatus->id; 
                $statusHistory->service_id = $service->id; 
                $statusHistory->note = "Service marked deleted";
                $statusHistory->save();

                // status history end
            }

            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function validateService(){
        return Validator::make(request()->all(), [
           'category_id' => 'required|exists:categories,id',
           'address'=>'required|string|max:100',
           'images'=>'nullable|mimes:jpeg,bmp,png,jpg',
           'description'=>'required|max:200',
           'status_id' => 'nullable|exists:statuses,id',
           'longitude'=>'nullable|string|max:20',
           'latitude'=>'nullable|string|max:20',
        ]);
    }

    public function validateUpdateService(){
        return Validator::make(request()->all(), [
           'category_id' => 'nullable|exists:categories,id',
           'address'=>'nullable|string|max:100',
           'images'=>'nullable|mimes:jpeg,bmp,png,jpg',
           'description'=>'nullable|max:200',
           'longitude'=>'nullable|string|max:20',
           'latitude'=>'nullable|string|max:20',
           'status_id' => 'nullable|exists:statuses,id',
        ]);
    }

    public function validateSuspendService(){
        return Validator::make(request()->all(), [
           'days'=>'required|integer|max:60',
           'note'=>'nullable|string|max:100',
        ]);
    }
}
