<?php

namespace App\Http\Controllers;

use App\Models\Category;
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


class CategoryController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategories(){

        try{
            $categories= Category::latest()->get();
            return $this->successResponse($categories);
        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
       
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getCategory($id) {

        try{
            $category= Category::where('id', $id)->firstOrFail();
            return $this->successResponse($category);
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
    public function addCategory(Request $request)
    {

        try{

            $validator = $this->validateCategory();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            //get form image
            $image= $request->file('image');
            $slug= Str::slug($request->name);

            if(isset($image)){
                $path = $image->getRealPath();
                $realImage = file_get_contents($path);
                $imageName = base64_encode($realImage);
            }else{
                $imageName="default.png";
            }

            if (Auth::check())
            {
                $id = Auth::id();
            }

            $category= new Category();
            $category->name= $request->name;
            $category->status_id= $request->status_id ? $request->status_id : '1';
            $category->slug=$slug;
            $category->description= $request->description;
            $category->created_by = $id;
            $category->image=$imageName;
            $category->save();

            return $this->successResponse($category,"Saved successfully", 200);

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
    public function updateCategory(Request $request, $id)
    {

        try{

            if(count($request->all()) == 0){
                return $this->errorResponse("Nothing to update.Pass fields", 404);  
            }

            $request->headers->set('Content-Type', '');

            $validator = $this->validateUpdateCategory();
            if($validator->fails()){
              return $this->errorResponse($validator->messages(), 422);
            }

            // get form image
            $image = $request->file('image');
            $category = Category::find($id);
            if (isset($image))
            {
                $path = $image->getRealPath();
                $realImage = file_get_contents($path);
                $imageName = base64_encode($realImage);

            } else {
                $imageName = $category->image;
            }

            if (Auth::check())
            {
                $id = Auth::id();
            }


            if($request->name){
              $slug = Str::slug($request->name);
              $category->name = $request->name;
              $category->slug = $slug;
            }

            if($request->status_id){
                $status = Status::where('id', $request->status_id)->firstOrFail();

                if(!$status){
                    return $this->errorResponse("Invalid status id", 404);  
                }

                $category->status_id= $status->id;
            }

            if($request->description){
              $category->description= $request->description;
            }

            $category->image = $imageName;
            $category->created_by = $id;
            $category->save();

            return $this->successResponse($category);
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
    public function deleteCategory($id)
    {
        try{
            $category = Category::find($id);
            $category->delete();

            return $this->successResponse(null,"Deleted successfully", 200);

        }catch(\Exception $e){
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function validateCategory(){
        return Validator::make(request()->all(), [
           'name'=>'required|unique:categories',
           'image'=>'required|mimes:jpeg,bmp,png,jpg',
           'description'=>'required|max:200',
           'status_id'=>'nullable|string',
        ]);
    }

    public function validateUpdateCategory(){
        return Validator::make(request()->all(), [
           'name'=>'nullable|unique:categories',
           'image'=>'nullable|mimes:jpeg,bmp,png,jpg',
           'description'=>'required|max:200',
           'status_id'=>'nullable|string',
        ]);
    }
}
