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
}
