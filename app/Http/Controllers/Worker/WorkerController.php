<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Morilog\Jalali\Jalalian;

class WorkerController extends Controller
{
    public function getCoWorkers(Request $request)
    {
        $coWorkers = Worker::where('parent_id', auth()->id())->paginate($request->per_page ?? 10);
        return response()->json(['status' => 'success', 'co_workers' => $coWorkers->all(), 'total_results' => $coWorkers->total()]);
    }

    public function createCoWorker(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'sex' => 'required|in:male,female|max:255',
            'birthdate' => 'required|date|max:255',
            'type' => 'required|in:secretary,stock',
            'phone' => 'required|numeric|digits_between:9,12|unique:workers',
            'email' => 'string|email|max:255|unique:workers',
            'password' => 'required|string|min:6'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }
        $worker = Worker::create(
            $request->only('firstname', 'lastname', 'sex', 'phone', 'email')
            + ['birthdate' => Jalalian::fromFormat('Y-m-d', $request->birthdate)->toCarbon()]
            + ['password' => Hash::make($request->password)]
            + ['parent_id' => auth()->id()]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Co-worker created successfully',
            'co_worker' => $worker
        ], 201);
    }

    public function getCoWorker(Worker $coWorker)
    {
        if ($coWorker->parent_id !== auth()->id()) return response()->json(['status' => 'error', 'error' => 'notfound'], 404);
        return response()->json(['status' => 'success', 'co_worker' => $coWorker]);
    }

    public function updateCoWorker(Worker $coWorker, Request $request)
    {
        if ($coWorker->parent_id !== auth()->id()) return response()->json(['status' => 'error', 'error' => 'notfound'], 404);
        $validator = \Validator::make($request->all(), [
            'firstname' => 'string|max:255',
            'lastname' => 'string|max:255',
            'sex' => 'in:male,female|max:255',
            'birthdate' => 'date|max:255',
            'type' => 'in:secretary,stock',
            'phone' => 'numeric|digits_between:9,12|unique:workers,phone,' . $coWorker->id,
            'email' => 'string|email|max:255|unique:workers,email,' . $coWorker->id,
            'password' => 'string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }

        $updateData = $request->only('firstname', 'lastname', 'sex', 'phone', 'email');
        if ($request->birthdate) {
            $updateData['birthdate'] = Jalalian::fromFormat('Y-m-d', $request->birthdate)->toCarbon();
        }
        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $coWorker->update($updateData);
        $coWorker = $coWorker->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Co-worker updated successfully',
            'co_worker' => $coWorker
        ]);
    }

    public function deleteCoWorker(Worker $coWorker)
    {
        if ($coWorker->parent_id !== auth()->id()) return response()->json(['status' => 'error', 'error' => 'notfound'], 404);
        $coWorker->delete();
        return response()->json(['status' => 'success']);
    }
}
