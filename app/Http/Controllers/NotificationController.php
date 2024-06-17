<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::all();
        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'date' => 'required|date',
            'category' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:4096',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('notifications');
        }

        $notification = Notification::create([
            'title' => $request->title,
            'content' => $request->content,
            'date' => $request->date,
            'category' => $request->category,
            'file_path' => $filePath,
        ]);

        return response()->json($notification, 201);
    }

    public function show($id)
    {
        $notification = Notification::findOrFail($id);
        return response()->json($notification);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'date' => 'required|date',
            'category' => 'required|string',
            'file' => 'nullable|file|mimes:pdf|max:4096',
        ]);

        $notification = Notification::findOrFail($id);

        if ($request->hasFile('file')) {
            if ($notification->file_path) {
                Storage::delete($notification->file_path);
            }
            $notification->file_path = $request->file('file')->store('notifications');
        }

        $notification->update($request->all());

        return response()->json($notification);
    }

    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        if ($notification->file_path) {
            Storage::delete($notification->file_path);
        }
        $notification->delete();
        return response()->json(null, 204);
    }
}
