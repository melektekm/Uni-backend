<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\SubmittedAssignment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'course_name' => 'required|string|max:255',
            'assignment_name' => 'required|string|max:255',
            'student_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:4096',
        ]);

        $path = $request->file('file')->store('assignments');

        $assignment = SubmittedAssignment::create([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name,
            'assignment_name' => $request->assignment_name,
            'student_name' => $request->student_name,
            'student_id' => $request->student_id,
            'file_path' => $path,
        ]);

        return response()->json(['message' => 'Assignment uploaded successfully', 'assignment' => $assignment], 201);
    }
}
