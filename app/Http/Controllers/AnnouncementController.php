<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
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

        return response()->json(['message' => 'Announcement posted successfully!', 'data' => $announcement], 201);
    }
    public function index(Request $request)
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($announcements);
    }

    /**
     * Delete an announcement.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return response()->json(['message' => 'Announcement not found'], 404);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully'], 200);
    }

}
