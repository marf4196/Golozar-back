<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskCategory;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function taskCategoriesIndex(Request $request)
    {
        $taskCategories = TaskCategory::paginate($request->per_page ?? 10);
        return response()->json(['status' => 'success', 'task_categoires' => $taskCategories->all(), 'total_results' => $taskCategories->total()]);
    }

    public function getTaskCategory(TaskCategory $taskCategory)
    {
        return response()->json(['status' => 'success', 'category' => $taskCategory]);
    }

    public function createTaskCategory(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }
        $taskCategory = new TaskCategory();
        $taskCategory->name = $request->name;
        $taskCategory->save();
        $taskCategory = $taskCategory->fresh();
        return response()->json(['status' => 'success', 'category' => $taskCategory], 201);
    }

    public function updateTaskCategory(TaskCategory $taskCategory, Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }
        $taskCategory->update(['name' => $request->name]);
        $taskCategory = $taskCategory->fresh();
        return response()->json(['status' => 'success', 'category' => $taskCategory]);
    }

    public function deleteTaskCategory(TaskCategory $taskCategory)
    {
        $taskCategory->delete();
        return response()->json(['status' => 'successs']);
    }
}
