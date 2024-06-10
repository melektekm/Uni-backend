<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use Illuminate\Support\Facades\Validator;

class TeacherAssignmentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|string',
            'assignmentName' => 'required|string',
            'assignmentDescription' => 'nullable|string',
            'dueDate' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:4096', // Max 4MB PDF file
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $filePath = $file ? $file->store('public/files') : null;

        $assignment = Assignment::create([
            'course_code' => $request->course_code,
            'assignmentName' => $request->assignmentName,
            'assignmentDescription' => $request->assignmentDescription,
            'dueDate' => $request->dueDate,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Assignment uploaded successfully',
            'assignment' => $assignment,
        ], 201);
    }
}
