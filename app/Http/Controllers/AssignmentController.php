<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Assignment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    // public function teacherUploadAssignment(Request $request)
    // {
    //     // Validate the incoming request data
    //     $validator = Validator::make($request->all(), [
    //         'course_id' => 'required|string',
    //         'Add_description' => 'nullable|string',
    //         'ass_name' => 'required|string',
    //         'file_path' => 'nullable|file|mimes:pdf|max:4096', // 4MB max size and only PDF files
    //         'due_date' => 'required|date',
    //     ]);

    //     if ($validator->fails()) {
    //         return response([
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     // Store the file if it exists
    //     $filePath = null;
    //     if ($request->hasFile('file')) {
    //         $filePath = $request->file('file')->store('public/files');
    //     }

    //     // Create the assignment
    //     $assignment = Assignment::create([
    //         'course_id' => $request->input('course_id'),
    //         'Add_description' => $request->input('Add_description'),
    //         'ass_name' => $request->input('ass_name'),
    //         'file_path' => $filePath,
    //         'due_date' => $request->input('dueDate'),
    //     ]);

    //     return response([
    //         'message' => "Assignment added successfully",
    //         'assignment' => $assignment,
    //     ], 200);
    // }

    // // The studentUploadAssignment method remains unchanged
    // public function studentUploadAssignment(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'assignment_id' => 'required|exists:assignments,id',
    //         'student_id' => 'required|exists:students,student_id',
    //         'file' => 'nullable|file|mimes:docx,pdf',
    //     ]);

    //     if ($validator->fails()) {
    //         return response([
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $filePath = null;
    //     if ($request->hasFile('file')) {
    //         $filePath = $request->file('file')->store('public/files');
    //     }

    //     $assignment = Assignment::find($request->input('assignment_id'));

    //     if (!$assignment) {
    //         return response([
    //             'message' => "Assignment not found",
    //         ], 404);
    //     }

    //     $student = Auth::user();
    //     $statusId = SubmittedAssignment::where('status', 'submitted')->value('id');
    //     $assignment->students()->syncWithoutDetaching([$student->student_id => ['status' => $statusId]]);

    //     $submission = SubmittedAssignment::create([
    //         'assignment_id' => $assignment->id,
    //         'student_id' => $student->student_id,
    //         'file_path' => $filePath,
    //     ]);

    //     return response([
    //         'message' => "Assignment submitted successfully",
    //         'submission' => $submission,
    //     ], 200);
    // }
}
