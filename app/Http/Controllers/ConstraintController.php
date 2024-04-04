<?php

namespace App\Http\Controllers;

use App\Models\Constraint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConstraintController extends Controller
{

    public function changeConstraint(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'employeeBreakfastOrderMaxAmount' => 'integer|nullable',
                'guestBreakfastOrderMaxAmount' => 'integer|nullable',
                'employeeLunchOrderMaxAmount' => 'integer|nullable',
                'guestLunchOrderMaxAmount' => 'integer|nullable',
                'breakfastOrderTimeStart' => 'date_format:H:i|nullable',
                'breakfastOrderTimeEnd' => 'date_format:H:i|nullable',
                'lunchOrderTimeStart' => 'date_format:H:i|nullable',
                'lunchOrderTimeEnd' => 'date_format:H:i|nullable',
                'orderOpened' => 'boolean|nullable',
            ]);
            
     


            DB::beginTransaction();

            if (isset($validatedData['employeeBreakfastOrderMaxAmount'])) {
                Constraint::where('constraint_name', 'EmployeeBreakfastOrderMaxAmount')->update([
                    'max_num' => $validatedData['employeeBreakfastOrderMaxAmount'],
                ]);
            }
            if (isset($validatedData['employeeLunchOrderMaxAmount'])) {
                Constraint::where('constraint_name', 'EmployeeLunchOrderMaxAmount')->update([
                    'max_num' => $validatedData['employeeLunchOrderMaxAmount'],
                ]);
            }

            if (isset($validatedData['guestBreakfastOrderMaxAmount'])) {
                Constraint::where('constraint_name', 'GuestBreakfastOrderMaxAmount')->update([
                    'max_num' => $validatedData['guestBreakfastOrderMaxAmount'],
                ]);
            }
            if (isset($validatedData['guestLunchOrderMaxAmount'])) {
                Constraint::where('constraint_name', 'GuestLunchOrderMaxAmount')->update([
                    'max_num' => $validatedData['guestLunchOrderMaxAmount'],
                ]);
            }

        if (isset($validatedData['breakfastOrderTimeStart']) && isset($validatedData['breakfastOrderTimeEnd'])) {
    $startTime = date('H:i', strtotime($validatedData['breakfastOrderTimeStart']));
    $endTime = date('H:i', strtotime($validatedData['breakfastOrderTimeEnd']));
    
    if ($startTime < $endTime) {
        Constraint::where('constraint_name', 'BreakfastOrderTime')->update([
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    } else {
        
        return response()->json(['message' => 'የቁርስ ትእዛዝ መጀመሪያ ሰአት ከቁርስ ትእዛዝ መጨረሻ ሰአት መብለጥ የለበትም። ፎርሙን አስተካክለው እንደገና ይሞክሩ ።', 'error' =>''], 422);
        
    
    }
}

if (isset($validatedData['lunchOrderTimeStart']) && isset($validatedData['lunchOrderTimeEnd']) && isset($validatedData['breakfastOrderTimeEnd'])) {
    $startTime = date('H:i', strtotime($validatedData['lunchOrderTimeStart']));
    $endTime = date('H:i', strtotime($validatedData['lunchOrderTimeEnd']));
        $breakfastEndTime = date('H:i', strtotime($validatedData['breakfastOrderTimeEnd']));
    
    if ($breakfastEndTime >  $startTime){
        return response()->json(['message' => 'የቁርስ  እና የምሳ ሰአት ማዘዣ ጊዜ የተለያየ መሆን አለበት ። ፎርሙን አስተካክለው እንደገና ይሞክሩ ።', 'error' =>''], 422);
    }
    
    if ($startTime < $endTime) {
        Constraint::where('constraint_name', 'LunchOrderTime')->update([
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    } else {
        return response()->json(['message' => 'የምሳ ትእዛዝ መጀመሪያ ሰአት ከምሳ ትእዛዝ መጨረሻ ሰአት መብለጥ የለበትም። ፎርሙን አስተካክለው እንደገና ይሞክሩ ።', 'error' =>''], 422);
        
    }
}


            if (isset($validatedData['orderOpened'])) {
                Constraint::where('constraint_name', 'OrderOpened')->update([
                    'isclosed' => !$validatedData['orderOpened'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'ገደቦች በተሳካ ሁኔታ ተዘምነዋል']);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['message' => 'ገደቦችን ማዘመን አልተሳካም።', 'error' => ''], 500);
        }
    }


    public function getConstraint()
    {
        try {
            $constraints = Constraint::all();

            $timeFormatMap = [
                'BreakfastOrderTime' => ['breakfastOrderTimeStart', 'breakfastOrderTimeEnd'],
                'LunchOrderTime' => ['lunchOrderTimeStart', 'lunchOrderTimeEnd'],
            ];

            foreach ($constraints as $constraint) {
                if (isset($timeFormatMap[$constraint->constraint_name])) {
                    [$startTimeField, $endTimeField] = $timeFormatMap[$constraint->constraint_name];
                    $constraint->{$startTimeField} = date('h:i A', strtotime($constraint->{$startTimeField}));
                    $constraint->{$endTimeField} = date('h:i A', strtotime($constraint->{$endTimeField}));
                }
            }

            $fieldsToReturn = [
                'employeeBreakfastOrderMaxAmount' => optional($constraints->where('constraint_name', 'EmployeeBreakfastOrderMaxAmount')->first())->max_num ?? null,
                'guestBreakfastOrderMaxAmount' => optional($constraints->where('constraint_name', 'GuestBreakfastOrderMaxAmount')->first())->max_num ?? null,
                'employeeLunchOrderMaxAmount' => optional($constraints->where('constraint_name', 'EmployeeLunchOrderMaxAmount')->first())->max_num ?? null,
                'guestLunchOrderMaxAmount' => optional($constraints->where('constraint_name', 'GuestLunchOrderMaxAmount')->first())->max_num ?? null,
                'breakfastOrderTimeStart' => optional($constraints->where('constraint_name', 'BreakfastOrderTime')->first())->start_time ?? null,
                'breakfastOrderTimeEnd' => optional($constraints->where('constraint_name', 'BreakfastOrderTime')->first())->end_time ?? null,
                'lunchOrderTimeStart' => optional($constraints->where('constraint_name', 'LunchOrderTime')->first())->start_time ?? null,
                'lunchOrderTimeEnd' => optional($constraints->where('constraint_name', 'LunchOrderTime')->first())->end_time ?? null,
                'orderOpened' => !optional($constraints->where('constraint_name', 'OrderOpened')->first())->isclosed ?? null,

            ];

            return response()->json(['constraints' => $fieldsToReturn]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'ገደቦችን ማምጣት አልተሳካም።', 'error' => $e->getMessage()], 500);
        }
    }



}
