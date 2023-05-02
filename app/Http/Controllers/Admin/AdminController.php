<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = Admin::paginate($request->per_page ?? 10);
        return response()->json(['status' => 'success', 'admins' => $admins->all(), 'total_results' => $admins->total()]);
    }

    public function get(Admin $admin)
    {
        return response()->json(['status' => 'success', 'admin' => $admin]);
    }

    public function create(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'string|email|max:255|unique:admins,email',
            'password' => 'required|string|min:6|max:255',
            'type' => 'required|in:admin,accountant,stock'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }
        $admin = new Admin();
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->type = $request->type;
        $admin->save();

        $admin = $admin->fresh();

        return response()->json(['status' => 'success', 'admin' => $admin], 201);
    }

    public function update(Admin $admin, Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'string|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'string|min:6|max:255',
            'type' => 'in:admin,accountant,stock'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => implode(',', $validator->messages()->all())], 400);
        }

        $updateData = $request->only('name', 'email', 'type');

        if ($request->password) {
            $updateData['password'] = Hash::make($request->password);
        }

        $admin->update($updateData);
        $admin = $admin->fresh();

        return response()->json(['status' => 'success', 'admin' => $admin]);
    }

    public function delete(Admin $admin)
    {
        $admin->delete();
        return response()->json(['status' => 'success']);
    }
}
