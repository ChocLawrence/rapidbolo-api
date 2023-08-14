<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getCountries(){
        try{
            $countries= Country::latest()->get();
            return $this->successResponse($countries);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getCountry($id) {

        try{
            $country= Country::where('id', $id)->get();
            return $this->successResponse($country);
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
    public function addCountry(Request $request)
    {
        try{

            $validator = $this->validateCountry();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $countryFound= Country::where('name',  $request->name)->first();

            if($countryFound){
                return $this->errorResponse("Country already registered", 422);
            }

            $country=new Country();
            $country->name= $request->name;
            $country->iso= $request->iso;
            $country->iso3= $request->iso3;
            $country->dial= $request->dial;
            $country->currency= $request->currency;
            $country->currency_name= $request->currency_name;
            $country->save();

            return $this->successResponse($country,"Saved successfully", 200);

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
    public function updateCountry(Request $request, $id)
    {

        try{

            $validator = $this->validateCountry();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $country=Country::findOrFail($id);

            if($request->name){
                $country->name = $request->name;
            }

            if($request->iso){
                $country->iso= $request->iso;
            }

            if($request->iso3){
                $country->iso3= $request->iso3;
            }

            if($request->dial){
                $country->dial= $request->dial;
            }

            if($request->currency){
                $country->currency= $request->currency;
            }

            if($request->currency_name){
                $country->currency_name= $request->currency_name;
            }

            $country->save();

            return $this->successResponse($country,"Updated successfully", 200);

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
    public function deleteCountry($id)
    {
        try{

            Country::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateCountry(){
        return Validator::make(request()->all(), [
            'name' => 'required|string',
            'iso' => 'required|string',
            'iso3' => 'required|string',
            'dial' => 'required|string',
            'currency' => 'nullable|string',
            'currency_name' => 'nullable|string',
        ]);
    }

}
