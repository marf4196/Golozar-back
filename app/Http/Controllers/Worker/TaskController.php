<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Task;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $workerID = auth()->id();
        if (auth()->user()->parent_id) $workerID = auth()->user()->parent_id;
        $tasks = Task::where('worker_id', $workerID)->with('medicines')->paginate($request->per_page ?? 10);
        return response()->json(['status' => 'success', 'tasks' => $tasks->all(), 'total_results' => $tasks->total()]);
    }

    public function get(Task $task)
    {
        $workerID = auth()->id();
        if (auth()->user()->parent_id) $workerID = auth()->user()->parent_id;
        if ($task->worker_id !== $workerID) return response()->json(['status' => 'error', 'error' => 'notfound'], 404);
        $task = $task->load('medicines');
        return response()->json(['status' => 'success', 'task' => $task]);
    }

    public function create(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'date' => 'required|date|max:255',
            'user_id' => 'required|numeric|exists:users,id',
            'task_category_id' => 'required|numeric|exists:task_categories,id',
            'medicines' => 'nullable|array',
            'medicines.*' => 'array',
            'medicines.*.medicine_id' => 'required|numeric|exists:medicines,id',
            'medicines.*.quantity' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }

        if ($request->medicines && $deficiencies = $this->checkMedicinesStock($request->medicines)) return response()->json(['status' => 'error', 'error' => 'medicines out of stock', 'errors' => $deficiencies]);

        $createData = request()->only('title', 'description', 'type', 'user_id', 'task_category_id');
        try {
            $createData['date'] = Jalalian::fromFormat('Y-m-d H:i:s', $request->date)->toCarbon();

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'error' => 'invalid date format, date should be like:1402-02-03 17:40:00'], 400);
        }
        $workerID = auth()->id();
        if (auth()->user()->parent_id) $workerID = auth()->user()->parent_id;
        $createData['worker_id'] = $workerID;
        $task = Task::create($createData);
        if ($request->medicines) {
            $medicinesData = [];
            foreach ($request->medicines as $med) {
                $medicinesData[$med['medicine_id']] = ['quantity' => $med['quantity']];
                $medicine = Medicine::findOrFail($med['medicine_id']);
                $medicine->update(['quantity' => $medicine->quantity - $med['quantity']]);
            }
            $task->medicines()->attach($medicinesData);
        }
        $task = $task->fresh()->load('medicines');
        return response()->json(['status' => 'success', 'task' => $task], 201);
    }

    public function checkMedicinesStock($medicines, $exceptTask = null)
    {
//        if ($exceptTask && !$exceptTask->medicines) {
//            $exceptTask = $exceptTask->load('medicines');
//        }
        $stockDeficiencies = [];
        foreach ($medicines as $med) {
            $medicine = Medicine::findOrFail($med['medicine_id']);
            $currentTaskMedicineUsage = 0;
            if ($exceptTask) {
                $taskMedicine = $exceptTask->medicines()->find($med['medicine_id']);
                $currentTaskMedicineUsage = $taskMedicine->pivot->quantity;
            }
            if ($medicine->quantity + $currentTaskMedicineUsage < $med['quantity']) {
                $stockDeficiencies[$med['medicine_id']] = 'Medicine ' . $medicine->name . ' out of stock. current stock: ' . $medicine->quantity;
            }
        }
        return count($stockDeficiencies) ? $stockDeficiencies : null;
    }

    public function update(Task $task, Request $request)
    {
        $workerID = auth()->id();
        if (auth()->user()->parent_id) $workerID = auth()->user()->parent_id;
        if ($task->worker_id !== $workerID) return response()->json(['status' => 'error', 'error' => 'notfound'], 404);

        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'date' => 'required|date|max:255',
            'user_id' => 'required|numeric|exists:users,id',
            'task_category_id' => 'required|numeric|exists:task_categories,id',
            'medicines' => 'nullable|array',
            'medicines.*' => 'array',
            'medicines.*.medicine_id' => 'required|numeric|exists:medicines,id',
            'medicines.*.quantity' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }

        if ($request->medicines && $deficiencies = $this->checkMedicinesStock($request->medicines, $task)) return response()->json(['status' => 'error', 'error' => 'medicines out of stock', 'errors' => $deficiencies]);

        $updateData = request()->only('title', 'description', 'type', 'user_id', 'task_category_id');
        try {
            $updateData['date'] = Jalalian::fromFormat('Y-m-d H:i:s', $request->date)->toCarbon();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'error' => 'invalid date format, date should be like:1402-02-03 17:40:00'], 400);
        }
        $task->update($updateData);
        if ($request->medicines) {
            $this->removeTaskMedicines($task);
            $medicinesData = [];
            foreach ($request->medicines as $med) {
                $medicinesData[$med['medicine_id']] = ['quantity' => $med['quantity']];
                $medicine = Medicine::findOrFail($med['medicine_id']);
                $medicine->update(['quantity' => $medicine->quantity - $med['quantity']]);
            }
            $task->medicines()->sync($medicinesData);
        } else {
            $task->medicines()->delete();
        }
        $task = $task->fresh()->load('medicines');
        return response()->json(['status' => 'success', 'task' => $task]);
    }

    public function removeTaskMedicines($task)
    {
        if (!$task->medicines) {
            $task = $task->load('medicines');
        }
        foreach ($task->medicines as $med) {
            $med->update(['quantity' => $med->quantity + $med->pivot->quantity]);
            $task->medicines()->detach($med->id);
        }
    }

    public function delete(Task $task)
    {
        $workerID = auth()->id();
        if (auth()->user()->parent_id) $workerID = auth()->user()->parent_id;
        if ($task->worker_id !== $workerID) return response()->json(['status' => 'error', 'error' => 'notfound'], 404);
        $task->delete();
        return response()->json(['status' => 'success']);
    }

    public function getCategories()
    {
        $categories = TaskCategory::all();
        return response()->json(['status' => 'success', 'categories' => $categories]);
    }
}
