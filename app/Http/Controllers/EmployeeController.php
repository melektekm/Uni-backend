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

    public function getTransactionOfEmployee($employeeID)
    {
        $id = $employeeID;
        $employee = Employee::findOrFail($id);

        $account = $employee->account;
        $accountId = $account->id;


        $perPage = 10;

        $transactions = Transaction::where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $transactions->getCollection()->transform(function ($transaction) {
            $transaction->append('created_at_for_humans');
            return $transaction;
        });

        return response()->json($transactions);
    }

    public function getAmountOfEmployee($employeeID)
    {

        $account = Account::where('employee_id', $employeeID)->first();

        if ($account) {

            $amount = $account->balance;
            return $amount;
        }

        return 1;
    }

    public function searchEmployeeByName(Request $request)
{
    $term = $request->term;

    $results = (new Employee())->searchEmployeeByNameM(Employee::query(), $term)->get();

    // Fetch account balances for the retrieved employees
    $employeeIds = $results->pluck('id')->toArray();
    $balances = Account::whereIn('employee_id', $employeeIds)->pluck('balance', 'employee_id')->toArray();

    // Add the account balances to the employee results
    $rankedResults = $results->map(function ($employee) use ($balances) {
        $employee['balance'] = $balances[$employee['id']] ?? 0;
        return $employee;
    });

    $rankedResults = $this->rankResults($rankedResults, $term);

    return response()->json($rankedResults);
}

    public function rankResults($results, $searchTerm)
    {
        foreach ($results as $result) {
            similar_text($result->name, $searchTerm, $percent);
            $result->score = $percent;
        }

        $resultsArray = $results->toArray();

        usort($resultsArray, function ($a, $b) {
            return $b['score'] - $a['score'];
        });

        return $resultsArray;
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
