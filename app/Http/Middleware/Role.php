<?php

namespace App\Http\Middleware;

use Closure;

use Auth;
use App\Traits\ApiResponser;

class Role
{

    use ApiResponser;
    

    public function handle($request, Closure $next, ... $roles)
    {
        // $user = $request->user();
        // dd($user);

        if (!Auth::check()) // I included this check because you have it, but it really should be part of your 'auth' middleware, most likely added as part of a route group.
         return $this->errorResponse("Not logged in", 422);

        $user = Auth::user();

        if($user->isAdmin())
            return $next($request);

        foreach($roles as $role) {
            // Check if user has the role This check will depend on how your roles are set up
            if($user->hasRole($role))
                return $next($request);
        }

        return $this->errorResponse("Unauthorized action", 422);
    }
}
