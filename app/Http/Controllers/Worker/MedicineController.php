<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $medicines = Medicine::paginate($request->per_page ?? 10);
        return response()->json(['status' => 'success', 'medicines' => $medicines->all(), 'total_results' => $medicines->total()]);
    }
}
