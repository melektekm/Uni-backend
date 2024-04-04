<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Employee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function depositMoney(Request $request, $employeeId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            $employee = Employee::findOrFail($employeeId);
            $account = $employee->account;
            $account->balance += $request->input('amount');
            $account->save();


            Transaction::create([
                'account_id' => $account->id,
                'type' => 'deposit',
                'amount' => $request->input('amount'),

            ]);


            DB::commit();

            return response()->json(['message' => 'ገንዘብ በተሳካ ሁኔታ ተቀምጧል', 'account' => $account]);
        } catch (\Exception $e) {

            DB::rollback();

            return response()->json([
                'message' => 'ገንዘብ ማስገባት አልተቻለም',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function withdrawMoney(Request $request, $employeeId)
    {

        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        try {

            DB::beginTransaction();

            $employee = Employee::findOrFail($employeeId);
            $account = $employee->account;

            if ($account->balance < $request->input('amount')) {
                return response()->json(['error' => 'በቂ ያልሆነ ገንዘብ'], 400);
            }

            $account->balance -= $request->input('amount');
            $account->save();

            Transaction::create([
                'account_id' => $account->id,
                'type' => 'withdrawal',
                'amount' => $request->input('amount'),

            ]);

            DB::commit();

            return response()->json(['message' => 'ገንዘብ በተሳካ ሁኔታ ወጥቷል።', 'account' => $account]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'ገንዘብ ማውጣት አልተሳካም።',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function addRefund(Request $request, $employeeId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($employeeId);
            $account = $employee->account;

            $account->balance += $request->input('amount');
            $account->save();

            Transaction::create([
                'account_id' => $account->id,
                'type' => 'refund',
                'amount' => $request->input('amount'),

            ]);

            DB::commit();

            return response()->json(['message' => 'ተመላሽ ገንዘብ በተሳካ ሁኔታ ታክሏል።', 'account' => $account]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'ተመላሽ ገንዘብ ማከል አልተሳካም።',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function deactivateAccount($accountId)
    {
        $account = Account::findOrFail($accountId);

        $account->status = 'closed';
        $account->save();

        return response()->json(['message' => 'መለያ በተሳካ ሁኔታ ተዘግቷል።', 'account' => $account]);
    }

    public function getAccountById($accountId)
    {
        $account = Account::findOrFail($accountId);

        return response()->json(['account' => $account]);
    }


    public function getAmountOfEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $amount = $employee->account->balance;
        return response()->json(['account' => $amount]);
    }
}
