<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::paginate($request->per_page ?? 10);
        return response()->json(['status'=>'success','users'=>$users->all(),'total_results'=>$users->total()]);
    }
}
