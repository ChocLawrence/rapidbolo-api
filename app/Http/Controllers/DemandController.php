<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Demand;
use App\Models\User;
use App\Models\Status;
use App\Models\StatusHistory;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\PaymentMethod;
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
            return $this->errorResponse ($e->getMessage(), 404);
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

            //get authenticated user
            if (Auth::check())
            {
                $id = Auth::id();
            }

            //check for duplicate requests

            $status = Status::where('name', 'pending')->firstOrFail();
            $demand= new Demand();

            //check if service provider is same as service requester
            $service = Service::where('id', $request->service_id)->firstOrFail();
            
            if($service->user_id == $id){
            return $this->errorResponse('You cannot request a service from one you provide', 422);
            }

            //end check if demander is provider

            // check if service provider has an active status.
            $serviceStatus = Status::where('name', $service->status_id)->firstOrFail();

            if($serviceStatus->name != "active"){
                return $this->errorResponse("Service currently unavailable", 422);
            }  
            //end check service status


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

            
            //demand obj
            $demand->status_id = $status->id; 
            $demand->address= $request->address;
            $demand->service_id = $request->service_id;
            $demand->description= $request->description;
            $demand->longitude= $request->longitude;
            $demand->latitude= $request->latitude;
            $demand->user_id = $id;
            $demand->images=$imagesName;
            $demand->save();

            //status history

            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $status->id; 
            $statusHistory->demand_id = $demand->id; 
            $statusHistory->note = "Demand created";
            $statusHistory->save();

            // status history end

            return $this->successResponse($demand,"Saved successfully", 200);

            //end check if demand is new or a transfer


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
    public function transferDemand(Request $request)
    {

        try{

            $validator = $this->validateTransferDemand();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            //get authenticated user
            if (Auth::check())
            {
                $id = Auth::id();
            }


            //check for duplicate requests

            //check if user has the right to transfer: auth user == user_id

            $demandToTransfer = Demand::findOrFail($request->demand_id);

            if($demandToTransfer->user_id != $id){
                return $this->errorResponse('Unauthorized action', 422);
            }


            $status = Status::where('name', 'pending')->firstOrFail();

            //check if service provider is same as service requester
            $service = Service::where('id', $request->service_id)->firstOrFail();
            
            if($service->user_id == $id){
              return $this->errorResponse('You cannot request a service from one you provide', 422);
            }

            //end check if demander is provider

            //check if demand is already in progress. if so, demand cannot be changed.
            $status = Status::where('id', $demandToTransfer->status_id)->firstOrFail();

            if($status->name != "pending" && $status->name != "incomplete" && $status->name != "cancelled" && $status->name != "accepted"){
                return $this->errorResponse("Demand can only be transferred if marked incomplete,accepted,pending or cancelled", 404); 
            }

            //check if demand_id present and valid
            $transferredStatus = Status::where('name', 'transferred')->firstOrFail();

            $demand= new Demand();
            $demand->status_id = $status->id; 
            $demand->address= $demandToTransfer->address;
            $demand->service_id = $demandToTransfer->service_id;
            $demand->description= $demandToTransfer->description;
            $demand->longitude= $demandToTransfer->longitude;
            $demand->latitude= $demandToTransfer->latitude;
            $demand->user_id = $id;
            $demand->images=$demandToTransfer->images;
            $demand->save();

            //update current demand status to "transferred".
            $demandToTransfer->status_id = $transferredStatus->id; 
            $demandToTransfer->save();

            //update complete.[Transferred demands should be placed on review--> SUPPORT]

            //status history for transferred demand

            $demandToTransferSH= new StatusHistory(); 
            $demandToTransferSH->status_id = $transferredStatus->id; 
            $demandToTransferSH->demand_id = $demand->id; 
            $demandToTransferSH->note = "Demand transferred";
            $demandToTransferSH->save();

            //status history end for transferred demand


            //status history for new demand
        
            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $status->id; 
            $statusHistory->demand_id = $demand->id; 
            $statusHistory->note = "Demand created from transfer";
            $statusHistory->save();
            
            // status history end

            // send notification

            return $this->successResponse($demand,"Your demand transfered successfully", 200);

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
    public function acceptDemand(Request $request, $id)
    {

        try{

            if(count($request->all()) > 0){
                return $this->errorResponse("ERROR", 404);  
            }

            $request->headers->set('Content-Type', '');

            if (Auth::check())
            {
                $userId = Auth::id();
            }
          

            $demand = Demand::findOrFail($id);

            //check if accepter is actually service provider
            $service = Service::where('id', $demand->service_id)->firstOrFail();

            if($service->id != $userId ){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            $acceptedStatus = Status::where('name', 'accepted')->firstOrFail();

            if($acceptedStatus->name == "accepted"){
               return $this->errorResponse("Demand already accepted", 422);
            }

            $demand->status_id= $acceptedStatus->id;
            $demand->save();

            //status history for accepted demand

            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $acceptedStatus->id; 
            $statusHistory->demand_id = $demand->id; 
            $statusHistory->note = "Demand accepted";
            $statusHistory->save();
            // status history end
  

            return $this->successResponse($demand, "Demand accepted successfully", 200);
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
    public function setStages(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateSetStages();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            $demand = Demand::findOrFail($id);

            //check if demand is already in progress. if so, demand details cannot be changed.
            $status = Status::where('id', $demand->status_id)->firstOrFail();

            if($status->name == "progress" || $status->name == "progress-p1" || $status->name == "progress-p2"){
                return $this->errorResponse("Demand already in progress, cannot be updated", 404); 
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            //check if user is actually service provider : set stages
            $service = Service::where('id', $demand->service_id)->firstOrFail();

            if($service->id != $userId ){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            if($request->p_stages){
              $demand->p_stages= $request->p_stages;
            }

            if($request->p1_amount){
              $demand->p1_amount= $request->p1_amount;
            }

            if($request->p2_amount){

            if($request->p_stages == 1){
                return $this->errorResponse("Second payment is applicable for 2 stage payments.", 404); 
            }
              $demand->p2_amount= $request->p2_amount;
            }

            // by setting the stages and the amount, the provider confirms the amount he wants
            $demand->p_cfm_amount= true;
            $demand->save();

            return $this->successResponse($demand);
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
    public function demanderConfirmAmount(Request $request, $id)
    {

        try{

            if(count($request->all()) > 0){
                return $this->errorResponse("Error", 404);  
            }

            $request->headers->set('Content-Type', '');


            $demand = Demand::findOrFail($id);

            // check if demand is already in progress. if so, demand details cannot be changed.
            $status = Status::where('id', $demand->status_id)->firstOrFail();

            if($status->name == "progress"){
                return $this->errorResponse("Demand already in progress, cannot be updated", 404); 
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            if($demand->user_id != $userId ){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            // check if demander has already confirmed amount
            if($demand->d_cfm_amount == true ){
                return $this->errorResponse("You have already confirmed", 404); 
            }

            $demand->d_cfm_amount= true;
            $demand->save();

            return $this->successResponse($demand, "Confirmed Amount Successfully", 200);

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
    public function providerConfirmJob(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateProviderConfirmJob();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            $demand = Demand::findOrFail($id);

            //check if demand is already in progress. if so, demand details cannot be changed.
            $status = Status::where('id', $demand->status_id)->firstOrFail();

            if($status->name == "progress"){
                return $this->errorResponse("Demand already in progress, cannot be updated", 404); 
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            //check if user is actually service provider : provider confirm job
            $service = Service::where('id', $demand->service_id)->firstOrFail();

            if($service->id != $userId ){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            if($request->deadline){
              $demand->deadline= $request->deadline;
            }

            $demand->save();

            //status history for accepted demand
            $progressStatus = Status::where('name', 'progress')->firstOrFail();

            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $progressStatus->id; 
            $statusHistory->demand_id = $demand->id; 
            $statusHistory->note = "Demand in progress";
            $statusHistory->save();
            // status history end


            return $this->successResponse($demand, "Confirmed successfully", 200);
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
    public function demanderMakePayment(Request $request, $id)
    {

        try{

            if(count($request->all()) > 0){
                return $this->errorResponse("Error", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateDemanderMakePayment();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            $demand = Demand::findOrFail($id);

            if($demand->user_id != $userId ){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            // cater for MOMO/ CASH PAYMENTS ACCORDINGLY
            // check preferred payment method for service provider

            $service = Service::where('id', $demand->service_id)->firstOrFail();
            $paymentMethod = PaymentMethod::findOrFail($service->payment_pref_id);
            $preferredMethod = $paymentMethod->slug;

            //get number of stages and check if amount matches
            $paymentAmount = $request->amount;
            $paymentStage = $request->stage ? $request->stage : 1;

            $demandStages = $demand->p_stages; //2

            $demand_p1_amount =  $demand->p1_amount;
            $demand_p2_amount =  $demand->p2_amount ? $demand->p2_amount : 0;

            $currentStatus = Status::where('id', $demand->status_id)->firstOrFail();
            $p1AmountPaidStatus = Status::where('name', 'progress-p1')->firstOrFail();  
            $p2AmountPaidStatus = Status::where('name', 'progress-p2')->firstOrFail();

            
            if($currentStatus->name == "progress"){

                //No payment has been made yet

                if($paymentStage == 2){
                    return $this->errorResponse("Invalid Payment stage: ".$paymentStage, 404);    
                }

               
                if($paymentAmount != $demand_p1_amount){
                    return $this->errorResponse("Incorrect amount: ".$demand_p1_amount, 404);     
                }

                if($preferredMethod== "cash"){

                    // set cash as been paid.
                    $demand->status_id = $p1AmountPaidStatus->id;

                    $statusHistory= new StatusHistory(); 
                    $statusHistory->status_id = $p1AmountPaidStatus->id; 
                    $statusHistory->demand_id = $demand->id; 
                    $statusHistory->note = "Stage 1 amount paid";
                    $statusHistory->save();

                    // end status history
                    
                }

            }else if($currentStatus->name == "progress-p1"){

                //first payment has been made

                if($paymentStage == 1){
                    return $this->errorResponse("Invalid Payment stage: ".$paymentStage, 404);    
                }

                // check if this second payment matches p2_amount

                if($paymentAmount != $demand_p2_amount){
                    return $this->errorResponse("Incorrect amount: ".$demand_p2_amount, 404);     
                }

                //make second payment
                if($preferredMethod== "cash"){

                    // set cash as been paid.
                    $demand->status_id = $p2AmountPaidStatus->id;

                    $statusHistory= new StatusHistory(); 
                    $statusHistory->status_id = $p2AmountPaidStatus->id; 
                    $statusHistory->demand_id = $demand->id; 
                    $statusHistory->note = "Stage 2 amount paid";
                    $statusHistory->save();
                    
                }

            }else if($currentStatus->name == "progress-p2"){
                // No need for any payment as last stage payment has already been made
                return $this->errorResponse("Service has already been paid for completely", 404); 
            } 

            //add transaction entry
            $transaction= new Transaction(); 
            $transaction->description = $statusHistory->note;
            $transaction->amount = $paymentAmount ; 
            $transaction->country_id = $request->country_id;
            $transaction->status_id =  $statusHistory->status_id;
            $transaction->transaction_type_id = $request->transaction_type_id;
            $transaction->payment_method_id = $paymentMethod->id;
            $transaction->demand_id = $demand->id; 
            $transaction->user_id = $userId; 
            $transaction->save();

            //end transaction entry;

           
            $demand->save();

            return $this->successResponse($demand, "Payment made successfully", 200);

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

            //check if demand is already in progress. if so, demand cannot be changed.
            $status = Status::where('id', $demand->status_id)->firstOrFail();

            if($status->name == "progress"){
                return $this->errorResponse("Demand already in progress, cannot be updated", 404); 
            }

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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Demand  $demand
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function demanderConfirmDelivery(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateDemanderConfirmDelivery();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            $demand = Demand::findOrFail($id);

            //check if demand is already in progress. if so, demand details cannot be changed.
            $status = Status::where('id', $demand->status_id)->firstOrFail();

            if($status->name != "progress-p1" || $status->name != "progress-p2" || $status->name != "progress"){
                return $this->errorResponse("Demand cannote be confirmed delivered if it never made any progress", 404); 
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            //check if user is actually demander:
            if($demand->user_id != $userId ){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            $deliveryStatus = Status::where('id', $request->status_id)->firstOrFail();

            if($request->d_note){
              $demand->d_note= $request->d_note;
            }

            if($request->status_id){
              $demand->status_id= $deliveryStatus->id;
            }

            // alert user to submit rating.
            $demand->save();

            //status history for service delivery 
            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $deliveryStatus->id; 
            $statusHistory->demand_id = $demand->id; 
            $statusHistory->note = "Demand " .$deliveryStatus->name;
            $statusHistory->service_id = $demand->service_id; 


            if($deliveryStatus->name == "incomplete"){
                //check for all incomplete status by SP and add 1
                $incompleteStatus = StatusHistory::where('status_id', $deliveryStatus->id)->get();
                $strikeCount = $incompleteStatus->count() + 1;

                if($strikeCount == 3){

                     // suspend service provider
                     $statusHistory->suspension_days = $request->days; 
                     $suspensionExpiration = Carbon::now()->addDays($request->days)->format('Y-m-d');
                     $statusHistory->suspension_exp = $suspensionExpiration;
                     // end suspend service provider

                     // send notification


                     //end send notification
                     
                }
            }

            $statusHistory->strike_count = $strikeCount ? $strikeCount : 0; 
            $statusHistory->save();
            // status history end

           


            return $this->successResponse($demand, "OK", 200);
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
    public function providerConfirmDelivery(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateProviderConfirmDelivery();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            $demand = Demand::findOrFail($id);

            if(!$demand->d_note){
                return $this->errorResponse("Provider cannot confirm delivery if demander hasn't", 404); 
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            //check if user is actually provider:
            $service = Service::where('id', $demand->service_id)->firstOrFail();

            if($service->id != $userId ){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            if($request->p_note){
              $demand->p_note= $request->p_note;
            }

            // alert user to submit rating for demander ?

            $demand->save();


            return $this->successResponse($demand, "OK", 200);
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
    public function cancelDemand(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Missing fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateCancelDemand();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            //service provider or demander can cancel

            $demand = Demand::findOrFail($id);

            //check if demand is already in progress. if so, demand details cannot be changed.
            $status = Status::where('id', $demand->status_id)->firstOrFail();

            if($status->name != "pending" && $status->name != "accepted" && $status->name != "progress"){
                return $this->errorResponse("Demand cannot be cancelled in these states", 404); 
            }

            if (Auth::check())
            {
                $userId = Auth::id();
            }

            //check if user is actually demander/ SP:
            $service = Service::where('id', $demand->service_id)->firstOrFail();

            if($demand->user_id != $userId && $service->id != $userId){
                return $this->errorResponse("Unauthorized action.", 404); 
            }

            $cancelledStatus = Status::where('name', 'cancelled')->firstOrFail();

            $demand->status_id = $cancelledStatus->id;
            $demand->save();

            //status history for service delivery 
            $statusHistory= new StatusHistory(); 
            $statusHistory->status_id = $cancelledStatus->id; 
            $statusHistory->demand_id = $demand->id; 
            $statusHistory->user_id = $userId;
            $statusHistory->note = $request->reason ? $request->reason :  $cancelledStatus->name;
            $statusHistory->save();
            // status history end


            return $this->successResponse($demand, "OK", 200);
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
           'description'=>'required|string|max:200',
           'longitude'=>'nullable|string|max:20',
           'latitude'=>'nullable|string|max:20',
        ]);
    }


    public function validateProviderConfirmJob(){
        return Validator::make(request()->all(), [
            'deadline' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);
    }

    public function validateDemanderConfirmDelivery(){
        return Validator::make(request()->all(), [
            'd_note' => 'nullable|string|max:100', 
            'status_id' => 'required|exists:status,id',
        ]);
    }

    public function validateProviderConfirmDelivery(){
        return Validator::make(request()->all(), [
            'p_note' => 'required|string|max:100',
        ]);
    }


    public function validateTransferDemand(){
        return Validator::make(request()->all(), [
           'service_id' => 'required|exists:services,id',
           'demand_id' => 'required|exists:demands,id',
        ]);
    }

    public function validateCancelDemand(){
        return Validator::make(request()->all(), [
           'reason' => 'nullable|string|max:100',
        ]);
    }


    public function validateDemanderMakePayment(){
        return Validator::make(request()->all(), [
            'amount'=>'required|int|max:300000',
            'country_id' => 'required|exists:countries,id',
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'stage'=>'nullable|int|min:1|max:2',
        ]);
    }

    public function validateSetStages(){
        return Validator::make(request()->all(), [
            'p_stages'=>'required|int|max:2',
            'p1_amount'=>'required|int',
            'p2_amount'=>'nullable|int',
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
