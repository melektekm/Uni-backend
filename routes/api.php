<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConstraintController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentOrderController;
use App\Http\Controllers\InventoryEntryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\GuestOrderController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InventoryRequestController;
use App\Http\Controllers\AssignmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScheduleRequestController;
use App\Http\Controllers\CourseMaterialController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('auth')->group(
    function () {
        //api/auth/ ..
        Route::prefix('admin')->group(
            function () {

            Route::post('/addEmployee', [AuthController::class, 'addEmployee'])->middleware(['auth:sanctum', 'admin']);
            Route::get('/getEmployee', [AuthController::class, 'getEmployee'])->middleware(['auth:sanctum', 'admin']);
            Route::get('/fetchStudents', [AuthController::class, 'fetchStudents'])->middleware(['auth:sanctum', 'admin']);
            Route::post('/updateEmployee/{id}', [AuthController::class, 'updateEmployee'])->middleware(['auth:sanctum', 'admin']);
            Route::post('/deleteEmployee/{id}', [AuthController::class, 'deleteEmployee'])->middleware(['auth:sanctum', 'admin']);
            Route::post('/resetEmployeePassword/{id}', [AuthController::class, 'resetEmployeePassword'])->middleware(['auth:sanctum', 'admin']);
            Route::post('/register', [AuthController::class, 'registerAdmin']);
            Route::post('/login', [AuthController::class, 'loginAdmin']);

            Route::post('/upload-course', [AuthController::class, 'courseUpload'])->middleware(['auth:sanctum', 'admin']);
        }
        );
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    }
);

Route::get('/searchEmployeeByname', [EmployeeController::class, 'searchEmployeeByName']);
Route::get('/getEmployeeById/{employeeId}', [EmployeeController::class, 'getEmployeeById']);
Route::post('/updateEmployee/{id}', [EmployeeController::class, 'updateEmployee']);

Route::get('/getEmployee', [AuthController::class, 'getEmployee']);
Route::get('/updateEmployee/{id}', [AuthController::class, 'updateEmployee']);

Route::delete('/deleteEmployee/{id}', [AuthController::class, 'deleteEmployee']);

Route::post('/submit-assignment', [AssignmentController::class, 'store']);



Route::middleware(['auth'])->group(function () {
    Route::post('/upload-assignment', [TeacherAssignmentController::class, 'uploadAssignment']);
    Route::get('/getallassignments', [TeacherAssignmentController::class, 'getAllAssignments']);
    // Route::post('/submit-assignment', [TeacherAssignmentController::class, 'submitAssignment']);
});
Route::post('/upload-assignment', [TeacherAssignmentController::class, 'uploadAssignment']);
Route::post('/upload-assignment', [TeacherAssignmentController::class, 'uploadAssignment']);
Route::get('/getallassignments', [TeacherAssignmentController::class, 'getAllAssignments']);
// Route::post('/submit-assignment', [TeacherAssignmentController::class, 'submitAssignment']);
Route::post('/filterassignments', [TeacherAssignmentController::class, 'filterAssignments']);

Route::middleware(['auth'])->group(function () {
    Route::get('/courses', [CourseController::class, 'fetchCourses']);
    Route::put('/enroll/{id}', [CourseController::class, 'enroll']);
});
Route::get('/courses', [CourseController::class, 'fetchCourses']);
Route::put('/enroll/{id}', [CourseController::class, 'enroll']);
Route::post('/upload-course', [CourseController::class, 'uploadCourse']);
Route::get('/course/name/{course_code}', [CourseController::class, 'getCourseName']);

Route::middleware(['auth'])->group(function () {
    Route::get('/get-schedules', [ScheduleController::class, 'index']);
    Route::post('/schedule-requests', [ScheduleRequestController::class, 'store']);
    Route::delete('/schedule-requests/{id}', [ScheduleRequestController::class, 'destroy']);
});
Route::post('/schedule-requests', [ScheduleRequestController::class, 'store']);
Route::get('/get-schedules', [ScheduleController::class, 'index']);
// Route::get('/schedule-requests', [ScheduleController::class, 'index']);
// Route::post('/schedule-requests', [ScheduleController::class, 'store']);
// Route::delete('/schedule-requests/{id}', [ScheduleController::class, 'destroy']);

// Route::post('/schedule-requests', [ScheduleRequestController::class, 'store']);


Route::middleware(['auth'])->group(function () {
    Route::post('/post-announcement', [AnnouncementController::class, 'store']);
    Route::get('/announcement-items-no-filter', [AnnouncementController::class, 'index']);
    Route::delete('/announcement-items/{id}', [AnnouncementController::class, 'destroy']);
});

Route::post('/post-announcement', [AnnouncementController::class, 'store']);
Route::get('/announcement-items-no-filter', [AnnouncementController::class, 'index']);
Route::delete('/announcement-items/{id}', [AnnouncementController::class, 'destroy']);

Route::middleware(['auth'])->group(function () {
    Route::post('/upload-material', [CourseMaterialController::class, 'uploadMaterial']);
    Route::get('/getmaterialcontent/{materialId}', [CourseMaterialController::class, 'getMaterialContent']);
});
Route::post('/upload-material', [CourseMaterialController::class, 'uploadMaterial']);

Route::get('/getallmaterials', [CourseMaterialController::class, 'getAllMaterials']);
Route::post('/filtermaterials', [CourseMaterialController::class, 'filterMaterials']);
Route::get('/getmaterialcontent/{materialId}', [CourseMaterialController::class, 'getMaterialContent']);
