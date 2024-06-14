<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CourseMaterial;
use Illuminate\Support\Facades\Storage;

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

    try {
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('public/files');
        }

        $courseMaterial = CourseMaterial::create([
            'course_code' => $request->course_code,
            'material_title' => $request->material_title,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Course material uploaded successfully',
            'material' => $courseMaterial,
            'file_url' => $filePath ? Storage::url($filePath) : null,
        ], 201);
    } catch (\Exception $e) {
        // Log the error
        \Log::error('Error uploading course material: ' . $e->getMessage());

        return response()->json(['error' => 'Failed to upload course material.'], 500);
    }
}
    public function getAllMaterials()
 {
        $materials = CourseMaterial::all();
        // Fetch all materials
        return response()->json( [ 'materials' => $materials ] );
    }

    public function filterMaterials(Request $request)
{
    $searchTerm = $request->input('searchTerm');

    $query = CourseMaterial::query();

    // Apply filter based on course name
    if ($searchTerm) {
        $query->where('course_name', 'like', '%' . $searchTerm . '%');
    }

    $filteredMaterials = $query->get();

    return response()->json(['filteredMaterials' => $filteredMaterials]);
}

// public function getMaterialContent($materialId)
// {
//     try {
//         $material = CourseMaterial::findOrFail($materialId); // Assuming Material is a model

//         // Assuming 'file_path' is a field in your material database table storing the file path
//         $filePath = $material->file_path;

//         // Download the file
//         return response()->download(storage_path("app/public/{$filePath}"));
//     } catch (\Exception $e) {
//         // Log the error for further investigation
//         \Log::error('Error fetching material content: ' . $e->getMessage());

//         // Return a JSON response with an error message
//         return response()->json(['error' => 'Failed to fetch material content.'], 500);
//     }
// }
// public function getMaterialContent($materialId)
// {
//     try {
//         $material = CourseMaterial::findOrFail($materialId); // Example: Assuming Material is a model

//         // Assuming 'file_path' is a field in your material database table storing the file path
//         $filePath = $material->file_path;

//         return response()->download(storage_path("app/public/{$filePath}")); // Download the file
//     } catch (\Exception $e) {
//         return response()->json(['error' => 'Failed to fetch material content.'], 500);
//     }
// }

}
