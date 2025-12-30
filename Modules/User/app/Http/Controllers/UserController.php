<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Transformers\UserResource;

class UserController extends Controller
{
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }
}
