<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{

    use ApiResponser;
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getRoles(){
        try{
            $roles= Role::latest()->get();
            return $this->successResponse($roles);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getRole($id) {

        try{
            $role= Role::where('id', $id)->get();
            return $this->successResponse($role);
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
    public function addRole(Request $request)
    {
        try{
            $validator = $this->validateRole();
            if($validator->fails()){
            return $this->errorResponse($validator->messages(), 422);
            }

            $role=new Role();
            $role->name= $request->name;
            $role->description= $request->description;
            $role->save();

            return $this->successResponse($role,"Saved successfully", 200);

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
    public function updateRole(Request $request, $id)
    {

        try{

            $validator = $this->validateRole();
            if($validator->fails()){
               return $this->errorResponse($validator->messages(), 422);
            }

            $role=Role::findOrFail($id);
        

            if($request->name){
              $role->name = $request->name;
            }
            
            if($request->description){
              $role->description = $request->description;
            }
            
            $role->save();

            return $this->successResponse($role,"Updated successfully", 200);

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
    public function deleteRole($id)
    {
        try{

            Role::findOrFail($id)->delete();
            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse( $e->getMessage(), 404);
        }
    }

    public function validateRole(){
        return Validator::make(request()->all(), [
            'name' => 'required|string|max:10'
        ]);
    }
}
