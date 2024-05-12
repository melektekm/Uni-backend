<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\Account;
use App\Models\Department;
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

                if ($request->role == "employee") {
                    Account::create([
                        'employee_id' => $employee->id,
                        'balance' => 0,
                        'status' => 'active',
                    ]);
                }

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
    public function deleteEmployee(
        Request $request,
        $id
    ) {
        $task = $request->input('task');

        try {
            $systemUser = Employee::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'ተጠቃሚ አልተገኘም።'], 404);
        }



        if ($task == 1) {
            try {
                $systemUser->status = 1;
                $systemUser->save();



                return response()->json(['message' => 'የሰራተኛው አካውንት ታግዷል።']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'እገዳው አልተሳካም።'], 500);
            }
        } else if ($task == 0) {


            try {
                $systemUser->status = 0;
                $systemUser->save();



                return response()->json(['message' => 'የሰራተኛው አካውንት ዳግም ተጀምሯል']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'ተጠቃሚ አልተገኘም።'], 500);
            }



        } else {

            try {
                $systemUser->password = null;
                $systemUser->save();

                return response()->json(['message' => 'የሰራተኛው አካውንት የይለፍ ቃል ተስተካክሏል']);
            } catch (\Exception $e) {
                return response()->json(['error' => 'ተጠቃሚ አልተገኘም።'], 500);
            }

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

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],

        ]);

        if ($validator->fails()) {
            $messages = [
                0 => "Wrong data entry, try again",
                1 => "ልክ ያልሆነ መረጃ አስገበተዋል።  እንደገና ይሞክሩ። ",
                2 => "ግጉይ ሓበሬታ ምእታው እንደገና ፈትን",
                3 => "Galtee deetaa dogoggoraa, irra deebi'ii yaali",
                4 => "Gelida xogta khaldan, isku day mar kale",


            ];

            return response(['message' => $messages], 400);

        }


        $employee = Employee::where('email', $request->email)->first();
        if (!$employee) {
            $messages = [
                0 => "The email is not registered by MinT. Please contact the concerned authority",

            ];

            return response(['message' => $messages], 400);

        }



        if ($employee && $employee->status) {
            $messages = [
                0 => "Your account has been banned. Please reach out to the authorities to request reinstatement",
                1 => "ይህ አካውንት ስለታገደ ድርጊቱን ማከናወን አልተቻለም። አካውንቱን ለማስከፈት የሚመለከተውን አካል ያነጋግሩ ።",
                2 => "ጸብጻብካ ተኣጊዱ እዩ ። ናብ ሃገሮም ኪምለሱ ንምሕታት በጃኻ ናብ ሰበ ስልጣን ሕተቶም ",
                3 => "Akkaawuntii keessan ugguramee jira. Hojiitti akka deebi'an gaafachuuf aanga'oota qunnamaa",
                4 => "Koontadaada waa la mamnuucay Fadlan la xiriir maamulka si aad u codsato dib u soo celinta",


            ];

            return response(['message' => $messages], 400);

        }


        $userEmployee = SystemUserEmployee::where('employee_id', $employee['id'])->first();


        if ($userEmployee) {
            $messages = [
                0 => "You are already registered. Please try logging in by visiting the login page.",
                1 => "እርስዎ አስቀድመው የተመዘገቡ ተጠቃሚ ነዎት። ወደ መግቢያ ገጽ በመሄድ ለመግባት ይሞክሩ።'",
                2 => "ድሮ ተመዝጊብካ ኣለኻ ። በጃኻ ናብቲ መእተዊ ገጽ ብምእታው ክትኣቱ ፈትን።",
                3 => "Duraan galmaa'aniiru. Mee fuula seensaa daawwachuudhaan seenuuf yaalaa.",
                4 => "Horay ayaad u diiwaan gashanayd Fadlan isku day inaad gasho adigoo booqanaya bogga gelitaanka",


            ];

            return response(['message' => $messages], 400);

        }
        $employee->password = Hash::make($request->password);
        $employee->save();

        $otpCode = rand(100000, 999999);


        $existingRecord = DB::table('otp_codes')
            ->where('email', $request->email)
            ->first();

        if ($existingRecord) {
            DB::table('otp_codes')
                ->where('email', $request->email)
                ->update([
                    'otp_code' => $otpCode,
                    'expires_at' => Carbon::now()->addMinutes(1000),
                    'updated_at' => Carbon::now()
                ]);
        } else {
            DB::table('otp_codes')->insert([
                'email' => $request->email,
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(1000),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        Mail::to($request->email)->send(new OtpMail($otpCode));

        return response([
            'message' => 'OTP sent to email',
            "success" => true
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],

        ]);

        if ($validator->fails()) {
            $messages = [
                0 => "Wrong data entry, try again",
                1 => "ልክ ያልሆነ መረጃ አስገበተዋል።  እንደገና ይሞክሩ። ",
                2 => "ግጉይ ሓበሬታ ምእታው እንደገና ፈትን",
                3 => "Galtee deetaa dogoggoraa, irra deebi'ii yaali",
                4 => "Gelida xogta khaldan, isku day mar kale",


            ];

            return response(['message' => $messages], 400);
        }


        $employee = Employee::where('email', $request->email)->first();

        if (!$employee) {
            $messages = [
                0 => "The email is not registered by MinT. Please contact the concerned authority",
                1 => "ይህ ኢሜል በሚንት የተመዘገበ አይደለም። መጀመሪያ በአስተዳዳሪ ይመዝገቡ።",
                2 => "እቲ ኢ-መይል ኣብ ሚንቲ ኣይተመዝገበን። በጃኻ ምስቲ ጕዳይ እተተሓሓዘ ብዓል ስልጣን ተራኸብ ።",
                3 => "Imeelichi MinT irratti hin galmaa'u. Qaama dhimmi ilaallatu qunnamaa.",
                4 => "Iimaylku kama diiwaan gashanayn MinT. Fadlan la xidhiidh maamulka ay khusayso.",


            ];

            return response(['message' => $messages], 400);
        }
        if ($employee && $employee->status) {
            $messages = [
                0 => "Your account has been banned. Please reach out to the authorities to request reinstatement",
                1 => "ይህ አካውንት ስለታገደ ድርጊቱን ማከናወን አልተቻለም። አካውንቱን ለማስከፈት የሚመለከተውን አካል ያነጋግሩ ።",
                2 => "ጸብጻብካ ተኣጊዱ እዩ ። ናብ ሃገሮም ኪምለሱ ንምሕታት በጃኻ ናብ ሰበ ስልጣን ሕተቶም ",
                3 => "Akkaawuntii keessan ugguramee jira. Hojiitti akka deebi'an gaafachuuf aanga'oota qunnamaa",
                4 => "Koontadaada waa la mamnuucay Fadlan la xiriir maamulka si aad u codsato dib u soo celinta",


            ];

            return response(['message' => $messages], 400);
        }

        $sysEmployee = SystemUserEmployee::where('employee_id', $employee->id)->first();
        if (!$sysEmployee) {

            $messages = [
                0 => "No account has been created with this email. Please try registering.",
                1 => "በዚህ ኢሜል የተከፈተ አካውንት የለም። ለመመዝገብ ይሞክሩ።",
                2 => "በዚ ኢ-መይል እዚ እተፈጥረ ሕሳብ የልቦን ክትምዝገብ ፈትን ",
                3 => "Akkaawuntii email kanaan uumame hin jiru, galmaa'uuf yaali",
                4 => "Ma jiro akoon uu sameeyay iimaylkan, isku day inaad isdiiwaangeliso",


            ];

            return response(['message' => $messages], 400);

        }


        $otpCode = rand(100000, 999999);


        $existingRecord = DB::table('otp_codes')
            ->where('email', $request->email)
            ->first();

        if ($existingRecord) {
            DB::table('otp_codes')
                ->where('email', $request->email)
                ->update([
                    'otp_code' => $otpCode,
                    'expires_at' => Carbon::now()->addMinutes(1000),
                    'updated_at' => Carbon::now()
                ]);
        } else {
            DB::table('otp_codes')->insert([
                'email' => $request->email,
                'otp_code' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(1000),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        Mail::to($request->email)->send(new OtpMail($otpCode));

        return response([
            'message' => 'OTP ወደ ኢሜል ተልኳል። መልክቱን "inbox" ውስጥ ካላገኙት "junks" ወይም "spam" ፎልደር ውስጥ ይመልከቱ። ',
            "success" => true
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|string',
            'otp_code' => 'required|string',
        ]);


        $otp = DB::table('otp_codes')->where('email', $request->email)->first();

        if (!$otp || $otp->otp_code != $request->otp_code || Carbon::now()->greaterThan($otp->expires_at)) {
            $messages = [
                0 => "The OTP entered is incorrect or has expired. Please request a new OTP.",
                1 => "ልክ ያልሆነ ወይም ጊዜው ያለፈበት OTP",
                2 => "እቲ ዝኣተወ ሉፕ ጌጋ ወይ ከኣ ጠፊኡ እዩ ። በጃኹም ሓድሽ OTP",
                3 => "OTP galfame sirrii miti ykn yeroon isaa darbe. Mee OTP haaraa gaafadhaa.",
                4 => "OTP-ga la geliyey waa khalad ama wuu dhacay. Fadlan codso OTP cusub",


            ];

            return response(['message' => $messages], 400);



        }

        $employee = Employee::where('email', $request->email)->first();

        if ($employee && $employee->status) {
            $messages = [
                0 => "Your account has been banned. Please reach out to the authorities to request reinstatement",
                1 => "ይህ አካውንት ስለታገደ ድርጊቱን ማከናወን አልተቻለም። አካውንቱን ለማስከፈት የሚመለከተውን አካል ያነጋግሩ ።",
                2 => "ጸብጻብካ ተኣጊዱ እዩ ። ናብ ሃገሮም ኪምለሱ ንምሕታት በጃኻ ናብ ሰበ ስልጣን ሕተቶም ",
                3 => "Akkaawuntii keessan ugguramee jira. Hojiitti akka deebi'an gaafachuuf aanga'oota qunnamaa",
                4 => "Koontadaada waa la mamnuucay Fadlan la xiriir maamulka si aad u codsato dib u soo celinta",


            ];

            return response(['message' => $messages], 400);
        }

        $employee->password = Hash::make($request->new_password);

        $employee->save();

        DB::table('otp_codes')->where('email', $request->email)->delete();

        return response([
            'message' => 'የይለፍ ቃል ዳግም ማስጀመር ተሳክቷል።',
            "success" => true
        ], 200);
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string',],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $messages = [
                0 => "Wrong data entry, try again",
                1 => "ልክ ያልሆነ መረጃ አስገበተዋል።  እንደገና ይሞክሩ። ",
                2 => "ግጉይ ሓበሬታ ምእታው እንደገና ፈትን",
                3 => "Galtee deetaa dogoggoraa, irra deebi'ii yaali",
                4 => "Gelida xogta khaldan, isku day mar kale",


            ];

            return response(['message' => $messages], 400);

        }

        $credentials = $request->only('email', 'password');

        $employee = Employee::where('email', $credentials['email'])->first();



        if (!$employee) {
            $messages = [
                0 => "The email is not registered by MinT. Please contact the concerned authority",
                1 => "ይህ ኢሜል በሚንት የተመዘገበ አይደለም። መጀመሪያ በአስተዳዳሪ ይመዝገቡ።",
                2 => "እቲ ኢ-መይል ኣብ ሚንቲ ኣይተመዝገበን። በጃኻ ምስቲ ጕዳይ እተተሓሓዘ ብዓል ስልጣን ተራኸብ ።",
                3 => "Imeelichi MinT irratti hin galmaa'u. Qaama dhimmi ilaallatu qunnamaa.",
                4 => "Iimaylku kama diiwaan gashanayn MinT. Fadlan la xidhiidh maamulka ay khusayso.",


            ];

            return response(['message' => $messages], 400);

        }
        if ($employee->status) {
            $messages = [
                0 => "Your account has been banned. Please reach out to the authorities to request reinstatement",
                1 => "ይህ አካውንት ስለታገደ ድርጊቱን ማከናወን አልተቻለም። አካውንቱን ለማስከፈት የሚመለከተውን አካል ያነጋግሩ ።",
                2 => "ጸብጻብካ ተኣጊዱ እዩ ። ናብ ሃገሮም ኪምለሱ ንምሕታት በጃኻ ናብ ሰበ ስልጣን ሕተቶም ",
                3 => "Akkaawuntii keessan ugguramee jira. Hojiitti akka deebi'an gaafachuuf aanga'oota qunnamaa",
                4 => "Koontadaada waa la mamnuucay Fadlan la xiriir maamulka si aad u codsato dib u soo celinta",


            ];

            return response(['message' => $messages], 400);

        }

        $systemUserEmployee = SystemUserEmployee::where('employee_id', $employee['id'])->first();


        if (!$systemUserEmployee || !Hash::check($credentials['password'], $employee->password)) {
            $messages = [
                0 => "Incorrect password, please try again.",
                1 => "የተሳሳተ የይለፍ ቃል አስገብተዋል።  እንደገና ይሞክሩ።",
                2 => "ግጉይ ቃላት በጃኻ እንደገና ፈትን።",
                3 => "Jecha icciitii sirrii hin taane, maaloo irra deebi'ii yaali.",
                4 => "Furaha sirta ah ee khaldan, fadlan isku day mar kale",


            ];

            return response(['message' => $messages], 400);

        }

        return $this->getResponse($systemUserEmployee);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response(
            "በተሳካ ሁኔታ ወጥቷል።",
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

    private function getResponse(SystemUserEmployee $systemUserEmployee)
    {
        $expiresAt = \Illuminate\Support\Carbon::now()->addYear();
        $token = $systemUserEmployee->createToken('employee_token', ['expires_at' => $expiresAt])->plainTextToken;


        $user = $systemUserEmployee->employee;
        $user->department = Department::where('id', $user->department)->first()->name;
        $user->accessToken = $token;

        return response([
            'tokenType' => 'Bearer',
            'user' => $user,
        ], 200);
    }

    public function getUser(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->employee->id,
            'name' => $user->employee->name,
            'department' => $user->employee->department,

            'email' => $user->employee->email,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|string',
        ]);


        $otp = DB::table('otp_codes')->where('email', $request->email)->first();

        if (!$otp || $otp->otp_code != $request->otp_code || Carbon::now()->greaterThan($otp->expires_at)) {
            $messages = [
                0 => "The OTP entered is incorrect or has expired. Please request a new OTP.",
                1 => "ልክ ያልሆነ ወይም ጊዜው ያለፈበት OTP",
                2 => "እቲ ዝኣተወ ሉፕ ጌጋ ወይ ከኣ ጠፊኡ እዩ ። በጃኹም ሓድሽ OTP",
                3 => "OTP galfame sirrii miti ykn yeroon isaa darbe. Mee OTP haaraa gaafadhaa.",
                4 => "OTP-ga la geliyey waa khalad ama wuu dhacay. Fadlan codso OTP cusub",


            ];

            return response(['message' => $messages], 400);

        }

        $employee = Employee::where('email', $request->email)->first();

        $systemUserEmployee = new SystemUserEmployee([
            'employee_id' => $employee->id,
        ]);
        $systemUserEmployee->save();

        DB::table('otp_codes')->where('email', $request->email)->delete();

        return $this->getResponse($systemUserEmployee);
    }


}
