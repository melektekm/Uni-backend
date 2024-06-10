<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseMaterial;

class CourseMaterialController extends Controller
{
    public function uploadMaterial(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'course_code' => 'required',
            'material_title' => 'required',
            'file' => 'required|file|mimes:pdf|max:4096', // Assuming PDF files are accepted up to 4 MB
        ]);

        // Upload the file
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('uploads', $fileName);

        // Save the course material
        $courseMaterial = new CourseMaterial();
        $courseMaterial->course_code = $validated['course_code'];
        $courseMaterial->material_title = $validated['material_title'];
        $courseMaterial->file_path = $filePath;
        $courseMaterial->save();

        return response()->json(['message' => 'Course material uploaded successfully']);
    }

    public function getCourseName(Request $request)
    {
        $courseCode = $request->input('course_code');

        // Fetch course name based on course code
        // Assuming you have the logic to fetch course name from your database
        $courseName = "Course Name"; // Placeholder for course name

        return response()->json(['course_name' => $courseName]);
    }
}
