<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function uploadCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|unique:courses',
            'course_name' => 'required',
            'course_description' => 'required',
            'credit_hours' => 'required|integer',
            'year' => 'required|integer',
            'semester' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name,
            'course_description' => $request->course_description,
            'credit_hours' => $request->credit_hours,
            'year' => $request->year,
            'semester' => $request->semester,
        ]);

        return response()->json(['message' => 'Course uploaded successfully!', 'course' => $course], 201);
    }
    public function getCourseName(Request $request, $course_code)
    {
        $course = Course::where('course_code', $course_code)->first();

        if (!$course) {
            return response()->json(['error' => 'Course not found.'], 404);
        }

        return response()->json(['course_name' => $course->course_name], 200);
    }

    // public function fetchCourses(Request $request)
    // {
    //     $currentPage = $request->input('page', 1);
    //     $itemsPerPage = 10;

    //     $courses = Course::paginate($itemsPerPage, ['*'], 'page', $currentPage);

    //     return response()->json([
    //         'courses' => $courses->items(),
    //         'current_page' => $courses->currentPage(),
    //         'lastPage' => $courses->lastPage(),
    //     ]);
    // }

    public function fetchCourses(Request $request)
    {
        $currentPage = $request->input('page', 1);
        $itemsPerPage = 10;

        $query = Course::query();

        if ($request->has('year') && !empty($request->input('year'))) {
            $query->where('year', $request->input('year'));
        }

        if ($request->has('semester') && !empty($request->input('semester'))) {
            $query->where('semester', $request->input('semester'));
        }

        $courses = $query->selectRaw('courses.*')->paginate($itemsPerPage, ['*'], 'page', $currentPage);

        return response()->json([
            'courses' => $courses->items(),
            'current_page' => $courses->currentPage(),
            'lastPage' => $courses->lastPage(),
        ]);
    }
}
