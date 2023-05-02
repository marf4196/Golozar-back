<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineStock;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $medicines = Medicine::paginate($request->per_page ?? 10);
        return response()->json(['status' => 'success', 'medicines' => $medicines->all(), 'total_results' => $medicines->total()]);
    }

    public function get(Medicine $medicine)
    {
        return response()->json(['status' => 'success', 'medicine' => $medicine]);
    }

    public function create(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric',
            'categories' => 'required|array',
            'categories.*' => 'numeric|exists:medicine_categories,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }

        $medicine = new Medicine();
        $medicine->name = $request->name;
        $medicine->quantity = $request->quantity;
        $medicine->save();
        $medicine->categories()->attach($request->categories);

        $medicine = $medicine->fresh()->load('categories');

        return response()->json(['status' => 'success', 'medicine' => $medicine], 201);
    }

    public function update(Medicine $medicine, Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'quantity' => 'required|numeric',
            'categories' => 'required|array',
            'categories.*' => 'numeric|exists:medicine_categories,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }
        $medicine->update(['name' => $request->name, 'quantity' => $request->quantity]);
        $medicine->categories()->sync($request->categories);

        $medicine = $medicine->fresh()->load('categories');

        return response()->json(['status' => 'success', 'medicine' => $medicine]);
    }

    public function delete(Medicine $medicine)
    {
        $medicine->delete();
        return response()->json(['status' => 'success']);
    }
}
