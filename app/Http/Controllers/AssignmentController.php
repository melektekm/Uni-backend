<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Assignment;
use App\Models\SubmittedAssignment;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{

    public function teacherUploadAssignment(Request $request)
    {
        $validator = $request->validate([
            'course_id' => 'required|string',
            'ass_description' => 'nullable|string',
            'ass_name' => 'required|string',
            'file' => 'nullable|file|mimes:docx,pdf',
            'due_date' => 'required|date',
        ]);

        $assignment = Assignment::create([
            'course_id' => $request->input('course_id'),
            'ass_description' => $request->input('ass_description'),
            'ass_name' => $request->input('ass_name'),
            'file_path' => $request->hasFile('file') ? $request->file('file')->store('public/files') : null,
            'due_date' => $request->input('due_date'),
        ]);

        return response([
            'message' => "Assignment added successfully",
            'assignment' => $assignment,
        ], 200);
    }



    public function studentUploadAssignment(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:_assignments,id',
            'student_id' => 'required|exists:students,student_id',
            'file' => 'nullable|file|mimes:docx,pdf',
        ]);

        if ($request->fails()) {
            return response([
                'errors' => $request->errors()
            ], 422);
        }

        $filePath = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('public/files');
        }

        $assignment = Assignment::find($request->input('assignment_id'));

        if (!$assignment) {
            return response([
                'message' => "Assignment not found",
            ], 404);
        }

        // Update the assignment status to "submitted" for the current student
        $student = Auth::user();
        $assignment->students()->syncWithoutDetaching([$student->student_id => ['status' => 'submitted']]);

        $submission = SubmittedAssignment::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->student_id,
            'file_path' => $filePath,
        ]);

        return response([
            'message' => "Assignment submitted successfully",
            'submission' => $submission,
        ], 200);
    }

}
