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
            $path = $request->file('file')->store('submitted_assignments');
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
    public function getAllSubmittedAssignments(Request $request)
    {
        // Assuming you have a model named Assignment
        $submittedAssignments = SubmittedAssignment::all();

        return response()->json(['assignments' => $submittedAssignments], 200);
    }

    public function getMaterialContent($materialId)
    {
        // Fetch the material from the database
        $material = SubmittedAssignment::find($materialId);

        if (!$material) {
            return response()->json(['error' => 'Material not found'], 404);
        }

        // Assuming the file path is stored in the `file_path` column of the material
        $filePath = $material->file_path;

        // Check if the file exists in the storage
        if (!Storage::exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Get the file content
        $fileContent = Storage::get($filePath);
        $fileMimeType = Storage::mimeType($filePath);
        $fileName = basename($filePath);

        // Return the file content as a response
        return response($fileContent, 200)
            ->header('Content-Type', $fileMimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

}
