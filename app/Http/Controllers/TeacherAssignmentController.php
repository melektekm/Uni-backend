<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class TeacherAssignmentController extends Controller
{
    public function uploadAssignment(Request $request)
    {
        $request->validate([
            'course_code' => 'required|string',
            'course_name' => 'required|string',
            'assignmentName' => 'required|string',
            'assignmentDescription' => 'nullable|string',
            'dueDate' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:4096', // Max 4MB PDF file
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('public/files');
        }

        $assignment = Assignment::create([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name, // Fixed to use correct field
            'assignmentName' => $request->assignmentName,
            'assignmentDescription' => $request->assignmentDescription,
            'dueDate' => $request->dueDate,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Assignment uploaded successfully',
            'assignment' => $assignment,
            'file_url' => $filePath ? Storage::url($filePath) : null,
        ], 201);
    }


    public function getAllAssignments(Request $request)
    {
        // Assuming you have a model named Assignment
        $assignments = Assignment::all();

        return response()->json(['assignments' => $assignments], 200);
    }
    public function getMaterialContent($materialId)
    {
        // Fetch the material from the database
        $material = Assignment::find($materialId);

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

    public function filterAssignments(Request $request)
    {
        $searchTerm = $request->input('searchTerm');

        $query = Assignment::query();

        // Apply filter based on course name
        if ($searchTerm) {
            $query->where('course_name', 'like', '%' . $searchTerm . '%');
        }

        $filteredAssignments = $query->get();

        return response()->json(['filteredAssignments' => $filteredAssignments]);
    }

}
