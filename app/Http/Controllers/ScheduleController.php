<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        return ScheduleRequest::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255',
            'classroom' => 'required|string|max:255',
            'labroom' => 'required|string|max:255',
            'classDays' => 'required|string|max:255',
            'labDays' => 'required|string|max:255',
            'labInstructor' => 'required|string|max:255',
            'classInstructor' => 'required|string|max:255',
            'scheduleType' => 'required|in:Exam,Class',
            'status' => 'nullable|string|max:255',
        ]);

        $scheduleRequest = ScheduleRequest::create($data);

        return response()->json([
            'message' => 'Schedule request submitted successfully!',
            'scheduleRequest' => $scheduleRequest,
        ], 201);
    }

    public function show($id)
    {
        return ScheduleRequest::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'course_name' => 'sometimes|required|string|max:255',
            'course_code' => 'sometimes|required|string|max:255',
            'classroom' => 'sometimes|required|string|max:255',
            'labroom' => 'sometimes|required|string|max:255',
            'classDays' => 'sometimes|required|string|max:255',
            'labDays' => 'sometimes|required|string|max:255',
            'labInstructor' => 'sometimes|required|string|max:255',
            'classInstructor' => 'sometimes|required|string|max:255',
            'scheduleType' => 'sometimes|required|in:Exam,Class',
        ]);

        $scheduleRequest = ScheduleRequest::findOrFail($id);
        $scheduleRequest->update($data);

        return response()->json($scheduleRequest, 200);
    }

    public function destroy($id)
    {
        $scheduleRequest = ScheduleRequest::findOrFail($id);
        $scheduleRequest->delete();

        return response()->json(null, 204);
    }
}
