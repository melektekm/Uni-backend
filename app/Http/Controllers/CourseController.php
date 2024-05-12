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
    public function courseUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string|unique:courses',
            'course_name' => 'required|string',
            'course_description' => 'required|string',
            'credit_hours' => 'required|integer',
            'year' => 'required|integer',
            'semester' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            Course::create([
                'course_code' => $request->input('course_code'),
                'course_name' => $request->input('course_name'),
                'course_description' => $request->input('course_description'),
                'credit_hours' => $request->input('credit_hours'),
                'year' => $request->input('year'),
                'semester' => $request->input('semester'),
                // Add more fields if needed
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Course uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred while uploading the course.');
        }
    }
}
