<?php

namespace App\Http\Controllers;

use App\Models\ScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScheduleRequestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.course_code' => 'required|string',
            'items.*.course_name' => 'required|string',
            'items.*.classroom' => 'required|string',
            'items.*.labroom' => 'nullable|string',
            'items.*.classDays' => 'required|string',
            'items.*.labDays' => 'nullable|string',
            'items.*.labInstructor' => 'nullable|string',
            'items.*.classInstructor' => 'required|string',
            'items.*.schedule_type' => 'required|string|in:Exam,Class',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $startId = null;
            $endId = null;

            $items = $request->input('items');

            foreach ($items as $item) {
                $scheduleReq = ScheduleRequest::create($item);

                if ($startId === null) {
                    $startId = $scheduleReq->id;
                }

                $endId = $scheduleReq->id;
            }

            DB::commit();

            return response()->json([
                'message' => 'Schedule requests created successfully',
                'start_id' => $startId,
                'end_id' => $endId
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to store schedule requests: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $scheduleType = $request->query('schedule_type');

        if ($scheduleType) {
            $schedules = Schedule::where('schedule_type', $scheduleType)->get();
        } else {
            $schedules = Schedule::all();
        }

        return response()->json($schedules);
    }
}
