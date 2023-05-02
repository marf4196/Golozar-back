<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ToothController extends Controller
{
    public function getTeeth()
    {
        $teeth = auth()->user()->teeth()->with('services','worker')->get();
        return response()->json(['status' => 'success', 'teeth' => $teeth]);
    }
}
