<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

Employee::unguard();
use Illuminate\Http\Request;
use App\Http\Resources\SystemUserEmployee as SysUserEmployee;

class EmployeeController extends Controller
{
    public function getAllEmployees(Request $request)
    {
        $perPage = $request->input('per_page', 20);

        $employees = Employee::paginate($perPage);

        $employees->getCollection()->transform(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'department' => $employee->department,
                'position' => $employee->position,
                'email' => $employee->email
            ];
        });

        return response()->json([
            'data' => $employees,
            'meta' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage()
            ]
        ]);
    }
    public function getEmployeeById($employeeID)
    {
        try {

            $employee = Employee::findOrFail($employeeID);

            return response()->json([
                'employeeFullName' => $employee->name,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Employee not found',
            ], 404);
        }
    }

    public function updateEmployee(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string'],
                'department' => ['required'],
                'position' => ['required', 'string'],
                // 'email' => ['required', 'string', 'email', "unique:employees"],
                //    'email' => ['required', 'string', 'email', 'regex:/^[a-zA-Z]+\.[a-zA-Z]+@mint\.gov\.et$/i'],
            ]
        );
        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }

        $employee = Employee::findOrFail($id);

        // $employee->update([
        //     'name' => $request->input('name'),
        //     'department' => $request->input('department'),
        //     'position' => $request->input('position'),
        //     'email' => $request->input('email'),
        // ]);
        $employee->name = $request->input('name');
        $employee->department = $request->input('department');
        $employee->position = $request->input('position');
        // $employee->email = $request->input('email');

        $employee->save();


        return response([
            'message' => "Employee edited successfully",
            'employee' => $employee->refresh(),
        ], 200);
    }

}
