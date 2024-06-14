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
        // Validate the request
        $request->validate([
            'course_name' => 'required|string|max:255',
            'assignment_name' => 'required|string|max:255',
            'student_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:4096',
        ]);

        // Store the file
        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('public/files');
        }


        // Create a new submitted assignment record
        $assignment = SubmittedAssignment::create([
            'course_name' => $request->course_name,
            'assignment_name' => $request->assignment_name,
            'student_name' => $request->student_name,
            'student_id' => $request->student_id,
            'file_path' => $path,
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Assignment uploaded successfully',
            'assignment' => $assignment,
        ], 201);
    }

}
