<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRequest;
use Illuminate\Http\Request;

class ScheduleRequestController extends Controller
{
    public function index()
    {
        return ScheduleRequest::all();
    }

    public function store(Request $request)
    {
        \Log::info('Schedule Request Data:', $request->all());

        $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:255',
            'classroom' => 'required|string|max:255',
            'labroom' => 'required|string|max:255',
            'classDays' => 'required|string|max:255',
            'labDays' => 'required|string|max:255',
            'labInstructor' => 'required|string|max:255',
            'classInstructor' => 'required|string|max:255',
            'scheduleType' => 'required|in:Exam,Class',
            'status' => 'required|in:Pending,Approved',
        ]);

        $scheduleRequest = ScheduleRequest::create($request->all());

        return response()->json($scheduleRequest, 201);
    }

    public function show($id)
    {
        return ScheduleRequest::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved',
        ]);

        $scheduleRequest = ScheduleRequest::findOrFail($id);
        $scheduleRequest->update($request->all());

        return response()->json($scheduleRequest, 200);
    }

    public function destroy($id)
    {
        ScheduleRequest::destroy($id);

        return response()->json(null, 204);
    }
}
