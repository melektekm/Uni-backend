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
use App\Http\Controllers\NotificationController;

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
            Route::get('getAllUsers', [AuthController::class, 'getAllUsers']);
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
Route::get('/getallsubmittedassignments', [AssignmentController::class, 'getAllSubmittedAssignments']);
Route::get('/getmaterialcontent/{materialId}', [AssignmentController::class, 'getMaterialContent']);




Route::middleware(['auth'])->group(function () {
    Route::get('/courses', [CourseController::class, 'fetchCourses']);
    Route::put('/enroll/{id}', [CourseController::class, 'enroll']);
});
Route::get('/courses', [CourseController::class, 'fetchCourses']);
Route::put('/enroll/{id}', [CourseController::class, 'enroll']);
Route::post('/upload-course', [CourseController::class, 'uploadCourse']);
Route::get('/course/name/{course_code}', [CourseController::class, 'getCourseName']);

Route::middleware(['auth'])->group(function () {
    Route::get('/schedule-requests', [ScheduleRequestController::class, 'index']);
    Route::post('/schedule-requests', [ScheduleRequestController::class, 'store']);
    Route::delete('/schedule-requests/{id}', [ScheduleRequestController::class, 'destroy']);
});

// Route::get('/schedule-requests', [ScheduleController::class, 'index']);
// Route::post('/schedule-requests', [ScheduleController::class, 'store']);
// Route::delete('/schedule-requests/{id}', [ScheduleController::class, 'destroy']);

Route::post('/schedule-requests', [ScheduleRequestController::class, 'store']);


Route::middleware(['auth'])->group(function () {
    Route::post('/post-announcement', [AnnouncementController::class, 'store']);
    Route::get('/announcement-items-no-filter', [AnnouncementController::class, 'index']);
    Route::delete('/announcement-items/{id}', [AnnouncementController::class, 'destroy']);
    Route::get('/announcement-items', [AnnouncementController::class, 'index']);
    Route::post('/announcement-items', [AnnouncementController::class, 'store']);
    Route::get('/announcement-items/{id}', [AnnouncementController::class, 'show']);
    Route::put('/announcement-items/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/announcement-items/{id}', [AnnouncementController::class, 'destroy']);
    Route::get('/announcement-items/content/{id}', [AnnouncementController::class, 'getAnnouncementContent']);
});

Route::post('/post-announcement', [AnnouncementController::class, 'store']);
Route::get('/announcement-items-no-filter', [AnnouncementController::class, 'index']);
Route::delete('/announcement-items/{id}', [AnnouncementController::class, 'destroy']);
Route::get('/announcement-items', [AnnouncementController::class, 'index']);
Route::post('/announcement-items', [AnnouncementController::class, 'store']);
Route::get('/announcement-items/{id}', [AnnouncementController::class, 'show']);
Route::put('/announcement-items/{id}', [AnnouncementController::class, 'update']);
Route::delete('/announcement-items/{id}', [AnnouncementController::class, 'destroy']);
Route::get('/announcement-items/content/{id}', [AnnouncementController::class, 'getAnnouncementContent']);



Route::middleware(['auth'])->group(function () {
    Route::post('/upload-material', [CourseMaterialController::class, 'uploadMaterial']);
    Route::get('/getmaterialcontent/{materialId}', [CourseMaterialController::class, 'getMaterialContent']);
});
Route::post('/upload-material', [CourseMaterialController::class, 'uploadMaterial']);

Route::get('/getallmaterials', [CourseMaterialController::class, 'getAllMaterials']);
Route::post('/filtermaterials', [CourseMaterialController::class, 'filterMaterials']);
// Route::get('/getmaterialcontent/{materialId}', [CourseMaterialController::class, 'getMaterialContent']);
// routes/web.php or routes/api.php
Route::get('/getmaterialcontent/{materialId}', [CourseMaterialController::class, 'getMaterialContent']);


Route::middleware(['auth'])->group(function () {
    Route::post('/upload-assignment', [TeacherAssignmentController::class, 'uploadAssignment']);
    Route::get('/getallassignments', [TeacherAssignmentController::class, 'getAllAssignments']);
    // Route::post('/submit-assignment', [TeacherAssignmentController::class, 'submitAssignment']);

    Route::get('/getassignmentcontent/{assignmentId}', [TeacherAssignmentController::class, 'getMaterialContent']);
});
Route::get('/getassignmentcontent/{assignmentId}', [TeacherAssignmentController::class, 'getMaterialContent']);
Route::get('/getassignmentcontent/${assignmentId}', [TeacherAssignmentController::class, 'getMaterialContent']);
Route::post('/upload-assignment', [TeacherAssignmentController::class, 'uploadAssignment']);
Route::get('/getallassignments', [TeacherAssignmentController::class, 'getAllAssignments']);
// Route::post('/submit-assignment', [TeacherAssignmentController::class, 'submitAssignment']);

Route::post('/filterassignments', [TeacherAssignmentController::class, 'filterAssignments']);




Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications', [NotificationController::class, 'store']);
Route::get('/notifications/{id}', [NotificationController::class, 'show']);
Route::put('/notifications/{id}', [NotificationController::class, 'update']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);