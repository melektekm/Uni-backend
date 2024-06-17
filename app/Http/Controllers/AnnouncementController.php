<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    // Store a new announcement
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'date' => 'required|date',
            'file' => 'nullable|file|mimes:pdf|max:4096', // 4MB max size
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('announcements');
        }

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'category' => $request->category,
            'date' => $request->date,
            'file_path' => $filePath,
        ]);

        return response()->json([
            'message' => 'Announcement posted successfully!',
            'data' => $announcement,
            'file_url' => $filePath ? Storage::url($filePath) : null,
        ], 201);
    }

    // Retrieve a paginated list of announcements
    public function index(Request $request)
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($announcements);
    }

    // Delete an announcement by ID
    public function destroy($id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['message' => 'Announcement not found'], 404);
        }

        // Delete associated file if it exists
        if ($announcement->file_path) {
            Storage::delete($announcement->file_path);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully'], 200);
    }

    // Retrieve the content of an announcement file
    public function getAnnouncementContent($announcementId)
    {
        try {
            $announcement = Announcement::findOrFail($announcementId);

            $filePath = storage_path('app/' . $announcement->file_path);

            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            return response()->file($filePath);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Announcement not found'], 404);
        }
    }

    // Show a single announcement by ID
    public function show($id)
    {
        $announcement = Announcement::findOrFail($id);
        return response()->json($announcement);
    }

    // Update an existing announcement
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'category' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
            'file' => 'nullable|file|mimes:pdf|max:4096', // 4MB max size
        ]);

        $announcement = Announcement::findOrFail($id);
        $announcement->title = $request->title ?? $announcement->title;
        $announcement->content = $request->content ?? $announcement->content;
        $announcement->category = $request->category ?? $announcement->category;
        $announcement->date = $request->date ?? $announcement->date;

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($announcement->file_path) {
                Storage::delete($announcement->file_path);
            }
            $filePath = $request->file('file')->store('announcements');
            $announcement->file_path = $filePath;
        }

        $announcement->save();

        return response()->json([
            'message' => 'Announcement updated successfully!',
            'data' => $announcement,
            'file_url' => $announcement->file_path ? Storage::url($announcement->file_path) : null,
        ]);
    }
}
