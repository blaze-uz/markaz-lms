<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        $teachers = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })->select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'teachers' => $teachers,
        ]);
    }

    public function show($id)
    {
        $user = \App\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })->select('id', 'name')
            ->where('id', $id)
            ->with(['roles' => function ($query) {
                $query->select('id', 'name');
            }])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }
}
