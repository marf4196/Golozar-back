<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function getTasks(Request $request)
    {
        $tasks = auth()->user()->tasks()->with('medicines')->limit($request->per_page ?? 15)->paginate();
        return response()->json(['status' => 'success', 'total_results' => $tasks->total(), 'tasks' => $tasks->all()]);
    }
}
