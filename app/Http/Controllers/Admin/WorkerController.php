<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Morilog\Jalali\Jalalian;

class WorkerController extends Controller
{
    public function index(Request $request)
    {
        $workers = Worker::paginate($request->per_page ?? 10);
        return response()->json(['status' => 'success', 'doctors' => $workers->all(), 'total_results' => $workers->total()]);
    }

    public function create(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'sex' => 'required|in:male,female|max:255',
            'birthdate' => 'required|date|max:255',
            'type' => 'required|in:doctor,secretary,stock',
            'phone' => 'required|numeric|digits_between:9,12|unique:workers',
            'email' => 'string|email|max:255|unique:workers',
            'password' => 'required|string|min:6',
            'parent_id' => 'nullable|numeric|exists:workers,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }
        $worker = Worker::create(
            $request->only('firstname', 'lastname', 'sex', 'type', 'phone', 'email', 'parent_id')
            + ['birthdate' => Jalalian::fromFormat('Y-m-d', $request->birthdate)->toCarbon()]
            + ['password' => Hash::make($request->password)]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Worker created successfully',
            'worker' => $worker
        ], 201);
    }

    public function get(Worker $worker)
    {
        return response()->json(['status' => 'success', 'doctor' => $worker]);
    }

    public function update(Worker $worker, Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firstname' => 'string|max:255',
            'lastname' => 'string|max:255',
            'sex' => 'in:male,female|max:255',
            'birthdate' => 'date|max:255',
            'type' => 'in:doctor,secretary,stock',
            'phone' => 'numeric|digits_between:9,12|unique:workers,phone,' . $worker->id,
            'email' => 'string|email|max:255|unique:workers,email,' . $worker->id,
            'password' => 'string|min:6',
            'parent_id' => 'nullable|numeric|exists:workers,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }

        $updateData = $request->only('firstname', 'lastname', 'sex', 'type', 'phone', 'email', 'parent_id');
        if ($request->birthdate) {
            $updateData['birthdate'] = Jalalian::fromFormat('Y-m-d', $request->birthdate)->toCarbon();
        }
        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        if (!$request->parent_id) {
            $updateData['parent_id'] = null;
        }

        $worker->update($updateData);
        $worker = $worker->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Worker updated successfully',
            'worker' => $worker
        ]);
    }

    public function delete(Worker $worker)
    {
        $worker->delete();
        return response()->json([
            'status' => 'success'
        ]);
    }
}
