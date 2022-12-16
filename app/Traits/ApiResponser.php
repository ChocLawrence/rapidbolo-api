<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser{

    protected function successResponse($data, $message = null, $code = null)
	{
		return response()->json([
			'status'=> 'success', 
			'message' => $message, 
			'data' => $data
		], $code ? $code: 200);
	}

	protected function errorResponse($message = null, $code = null)
	{
		return response()->json([
			'status'=>'error',
			'message' => $message,
			'data' => null
		], $code ? $code: 500);
	}

}