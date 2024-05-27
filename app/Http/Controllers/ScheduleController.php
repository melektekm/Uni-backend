<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $scheduleRequests = ScheduleRequest::where('requested_by', Auth::id())->get();
        return response()->json($scheduleRequests);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_name' => 'required|string',
            'course_code' => 'required|string',
            'classroom' => 'nullable|string',
            'labroom' => 'nullable|string',
            'class_days' => 'nullable|string',
            'lab_days' => 'nullable|string',
            'lab_instructor' => 'nullable|string',
            'class_instructor' => 'nullable|string',
            'schedule_type' => 'required|string',
        ]);

        $data['requested_by'] = Auth::id();
        $scheduleRequest = ScheduleRequest::create($data);

        return response()->json($scheduleRequest, 201);
    }

    public function destroy($id)
    {
        $scheduleRequest = ScheduleRequest::findOrFail($id);
        if ($scheduleRequest->requested_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $scheduleRequest->delete();
        return response()->json(['message' => 'Schedule request deleted successfully']);
    }
}
