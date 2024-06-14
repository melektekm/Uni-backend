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
            'course_code' => 'required|string',
            'course_name' => 'required|string',
            'material_title' => 'required|string',
            'file' => 'required|file|mimes:pdf|max:4096', // Assuming PDF files are accepted up to 4 MB
        ]);
    
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('public/files');
        }
    
        $courseMaterial = CourseMaterial::create([
            'course_code' => $request->course_code,
            'course_name' => $request->course_name,
            'material_title' => $request->material_title,
            'file_path' => $filePath,
        ]);
    
        return response()->json([
            'message' => 'Course Material uploaded successfully',
            'material' => $courseMaterial,
            'file_url' => $filePath ? Storage::url($filePath) : null,
        ], 201);
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

public function getMaterialContent($materialId)
{
    try {
        $material = CourseMaterial::findOrFail($materialId);
        $filePath = $material->file_path;

        if (!Storage::disk('public')->exists($filePath)) {
            \Log::error('File not found: ' . $filePath);
            return response()->json(['error' => 'File not found.'], 404);
        }

        return response()->file(storage_path("app/public/{$filePath}"));
    } catch (\Exception $e) {
        \Log::error('Error fetching material content: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch material content.'], 500);
    }
}


}
