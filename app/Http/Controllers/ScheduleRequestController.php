<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ScheduleRequest;

class ScheduleRequestController extends Controller
 {
    // Store method in ScheduleRequestController

    public function store( Request $request )
 {
        // Log the entire request payload for debugging
        Log::info( 'Received payload:', $request->all() );

        // Validation rules
        $validator = Validator::make( $request->all(), [
            'scheduleRequests' => 'required|array',
            'scheduleRequests.*.course_code' => 'required|string|exists:courses,course_code',
            'scheduleRequests.*.course_name' => 'required|string',
            'scheduleRequests.*.yearGroup' => 'required|string',
            'scheduleRequests.*.year' => 'required|integer',
            'scheduleRequests.*.schedule_type' => 'required|string|in:Exam,Class',
            'scheduleRequests.*.classroom' => 'required_if:scheduleRequests.*.schedule_type,Class|string|nullable',
            'scheduleRequests.*.labroom' => 'nullable|string',
            'scheduleRequests.*.classDays' => 'nullable|string', // Allow classDays to be nullable
            'scheduleRequests.*.labDays' => 'nullable|string',
            'scheduleRequests.*.labInstructor' => 'nullable|string',
            'scheduleRequests.*.classInstructor' => 'required_if:scheduleRequests.*.schedule_type,Class|string|nullable',
            'scheduleRequests.*.examDate' => 'required_if:scheduleRequests.*.schedule_type,Exam|date_format:Y-m-d|nullable',
            'scheduleRequests.*.examTime' => 'required_if:scheduleRequests.*.schedule_type,Exam|date_format:H:i|nullable',
            'scheduleRequests.*.examRoom' => 'required_if:scheduleRequests.*.schedule_type,Exam|string|nullable',
            'scheduleRequests.*.examiner' => 'required_if:scheduleRequests.*.schedule_type,Exam|string|nullable',
        ] );

        // Check for validation errors
        if ( $validator->fails() ) {
            Log::error( 'Validation failed:', $validator->errors()->toArray() );
            return response()->json( [ 'errors' => $validator->errors() ], 422 );
        }

        // Start a database transaction
        // Start a database transaction
        DB::beginTransaction();

        try {
            // Your code to create ScheduleRequest models

            // Commit the transaction
            DB::commit();

            // Return success response
            return response()->json( [
                'message' => 'Schedule requests created successfully',
                'start_id' => $startId,
                'end_id' => $endId
            ], 201 );
        } catch ( \Exception $e ) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            Log::error( 'Failed to store schedule requests:', [ 'exception' => $e ] );

            // Return error response
            return response()->json( [ 'error' => 'Failed to store schedule requests: ' . $e->getMessage() ], 500 );
        }
    }

    public function displaySchedule( Request $request )
 {
        $scheduleType = $request->query( 'schedule_type' );

        if ( $scheduleType ) {
            $schedules = ScheduleRequest::where( 'schedule_type', $scheduleType )->get();
        } else {
            $schedules = ScheduleRequest::all();
        }

        return response()->json( $schedules );
    }

    public function index( Request $request )
 {
        // Fetching the schedule requests with pagination and optional date filter
        $query = ScheduleRequest::query();

        if ( $request->has( 'date' ) ) {
            $query->whereDate( 'created_at', $request->input( 'date' ) );
        }

        $scheduleRequests = $query->paginate( $request->input( 'limit', 10 ) );

        return response()->json( $scheduleRequests );
    }

    public function approve( $id )
 {
        return $this->changeStatus( $id, 'approved' );
    }

    public function reject( $id )
 {
        return $this->changeStatus( $id, 'rejected' );
    }

    private function changeStatus( $id, $status )
 {
        try {
            $scheduleRequest = ScheduleRequest::findOrFail( $id );
            $scheduleRequest->status = $status;
            $scheduleRequest->save();

            return response()->json( [ 'message' => 'Request ' . $status . ' successfully.' ], 200 );
        } catch ( \Exception $e ) {
            Log::error( 'Failed to change status:', [ 'exception' => $e ] );
            return response()->json( [ 'error' => 'Failed to change status: ' . $e->getMessage() ], 500 );
        }
    }
}
