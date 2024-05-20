<?php

namespace App\Http\Controllers;


use App\Models\Employee;
use App\Models\Student;
use App\Models\SystemUserEmployee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function authFailed()
    {
        return response('unauthenticated', 401);
    }

    // public function addEmployee(Request $request)
    // {
    //     $validator = Validator::make(
    //         $request->all(),
    //         [
    //             'name' => ['required', 'string'],
    //             'role' => ['required'],
    //             'email' => ['required', 'string', 'unique:employees'],
    //             //make the email to account or user
    //         ]
    //     );


    //     $validator->sometimes('email', 'email', function ($input) {
    //         return $input->role === 'employee';
    //     });

    //     if ($validator->fails()) {
    //         return response(['errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         $employee = Employee::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'role' => $request->role ?? 'employee',
    //             'password' => null,
    //         ]);
    //         if ($request->role == "employee") {


    //             Account::create([
    //                 'employee_id' => $employee->id,
    //                 'balance' => 0,
    //                 'status' => 'active',
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'ሰራተኛ በተሳካ ሁኔታ ገብቷል።',
    //             'employee' => $employee,
    //         ], 200);
    //     } catch (\Exception $e) {

    //         DB::rollback();

    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'ሰራተኛ ማስገባት አልተሳካም።',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function addEmployee(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string'],
                'role' => ['required'],
                'email' => ['required', 'string', 'unique:employees'],
            ]
        );

        $validator->sometimes('email', 'email', function ($input) {
            return $input->role === 'employee';
        });

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->role === 'student') {
                $student = Student::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    // 'role' => $request->role,
                    'password' => null,
                ]);

                // Additional logic specific to students can be added here
                // For example, you can create a record in the 'students' table

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'user added succesfully',
                    'student' => $student,
                ], 200);
            } else {
                $employee = Employee::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'role' => $request->role ?? 'employee',
                    'password' => null,
                ]);



                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'user added',
                    'employee' => $employee,
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => 'user not added',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getEmployee(Request $request)
    {
        $request->validate([
            'page' => 'integer|min:1',
        ]);

        $perPage = $request->input('per_page', 20);

        $employees = DB::table('employees')
            ->join('departments', 'employees.department', '=', 'departments.id')
            ->leftJoin('accounts', 'employees.id', '=', 'accounts.employee_id')
            ->select('employees.id', 'employees.name', 'employees.role', 'employees.status', 'employees.email', 'departments.name as department', 'departments.id as departmentId', 'employees.created_at', 'accounts.balance')
            ->paginate($perPage);

        return response()->json($employees);
    }

    public function updateEmployee(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string'],
                'department' => ['required'],
                'role' => ['required'],
                'email' => ['required', 'string', "unique:employees,email,{$id}"],
            ]
        );


        // $validator->sometimes('email', 'email', function ($input) {
        //     return $input->role === 'employee';
        // });

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }


        $employee = Employee::findOrFail($id);

        $employee->update([
            'name' => $request->input('name'),
            'department' => $request->input('department'),
            'role' => $request->role ?? 'employee',
            'email' => $request->input('email'),
        ]);

        return response([
            'message' => "Employee edited successfully",
            'employee' => $employee->refresh(),
        ], 200);
    }
    public function fetchStudents()
    {
        try {
            $students = Student::all();

            return response()->json([
                'status' => 'success',
                'students' => $students,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch students.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerAdmin(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => ['required', 'string'],
                'role' => ['required',],
                'email' => ['required', 'string',],
                'password' => ['required', 'string']
            ]
        );

        if ($validator->fails()) {
            return response(
                [
                    'errors' => "Wrong credentails. Enter correct information "
                ],
                422
            );
        }


        $adminEmails = ['admin.admin@aau.edu.et'];
        try {
            $employee = Employee::where('email', $request->email)->first();
            if ($employee) {
                if ($employee->status) {
                    return response([
                        'errors' => 'This account is inactive. Contact the appropriate personell '
                    ], 422);

                }

                $isReset = $employee->password != null;
                if ($employee && $isReset) {
                    return response([
                        'errors' => 'There is an account With this email '
                    ], 422);
                }
            }

            if (in_array($request->email, $adminEmails)) {



                $role = 'admin';
                if (!$employee) {
                    $employee = Employee::create([
                        'name' => 'Super Admin',
                        'password' => Hash::make($request['password']),
                        'role' => $request['role'],
                        'email' => $request['email'],
                        // 'role' => $role,
                    ]);
                } else {

                    $employee->update([
                        'name' => 'Super Admin',
                        'password' => Hash::make($request['password']),
                        'role' => $request['role'],
                        'email' => $request['email'],
                        // 'role' => $role,
                    ]);
                }

                return $this->getAdminResponse($employee);
            }
            if (!$employee) {
                return response([
                    'errors' => 'This account hasnt been registered with the system. contact the school Registrar to be Registered '
                ], 422);

            }

            $employee->update([

                'password' => Hash::make($request['password']),
                'role' => $request['role'],
                'email' => $request['email'],

            ]);

            return $this->getAdminResponse($employee);


        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return response([
                // 'errors' => "የቴክኒክ ችግር አጋጥሟል። መረጃውን አስተካክለው እንደገና ይሞክሩ።"
                'errors' => $errorMessage
            ], 500);


        }
    }



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response(
            "Logged out",
            200
        );

    }

    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $employee = $request->user();
            if ($employee->status) {
                return response(
                    [
                        'errors' => 'This account is inactive. Contact the appropriate personell',
                    ],
                    422
                );
            }

            if ($employee->role === 'admin') {
                return $this->getAdminResponse($employee);
            } elseif ($employee->role === 'coordinator') {
                return $this->getAdminResponse($employee);
            } elseif ($employee->role === 'student') {
                return $this->getAdminResponse($employee);
            } elseif ($employee->role === 'dean') {

                return $this->getAdminResponse($employee);
            } elseif ($employee->role === 'instructor') {

                return $this->getAdminResponse($employee);
            }
        } else {
            return response(
                [
                    'errors' => 'የተሳሳተ መረጃ',
                ],
                422
            );
        }
    }

    private function getAdminResponse(Employee $employee)
    {
        $expiresAt = \Illuminate\Support\Carbon::now()->addYear();
        $token = $employee->createToken('employee_token', ['expires_at' => $expiresAt])->plainTextToken;

        return response([
            'user' => $employee,
            'accessToken' => $token,
            'tokenType' => 'Bearer',
        ], 200);
    }








}
