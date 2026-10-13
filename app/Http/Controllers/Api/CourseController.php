<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = \App\Models\Course::select('id', 'name', 'description')->get();
        return response()->json([
            'success' => true,
            'courses' => $courses,
        ]);
    }

    public function show($id)
    {
        $course = \App\Models\Course::select('id', 'name', 'description')
            ->where('id', $id)
            ->firstOrFail();
        return response()->json([
            'success' => true,
            'course' => $course,
        ]);
    }
}
