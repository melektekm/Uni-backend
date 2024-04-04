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
use App\Http\Controllers\InventoryRequestController;
use App\Http\Controllers\stockRequestController;
use Illuminate\Support\Facades\Route;



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
            Route::post('/updateEmployee/{id}', [AuthController::class, 'updateEmployee'])->middleware(['auth:sanctum', 'admin']);
            Route::post('/deleteEmployee/{id}', [AuthController::class, 'deleteEmployee'])->middleware(['auth:sanctum', 'admin']);
            Route::post('/resetEmployeePassword/{id}', [AuthController::class, 'resetEmployeePassword'])->middleware(['auth:sanctum', 'admin']);
            Route::post('/register', [AuthController::class, 'registerAdmin']);
            Route::post('/login', [AuthController::class, 'loginAdmin']);
        }
        );
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
        Route::get('/user', [AuthController::class, 'getUser'])->middleware('auth:sanctum');
        Route::get('authentication-failed', [AuthController::class, 'authFailed'])->name('auth-failed');
    }
);

Route::get('/searchEmployeeByname', [EmployeeController::class, 'searchEmployeeByName']);
Route::get('/getEmployeeById/{employeeId}', [EmployeeController::class, 'getEmployeeById']);
Route::get('/employee/transactions/{employeeId}', [EmployeeController::class, 'getTransactionOfEmployee']);
Route::get('/employee/amount/{employeeId}', [EmployeeController::class, 'getAmountOfEmployee']);
Route::post('/updateEmployee/{id}', [EmployeeController::class, 'updateEmployee']);

Route::get('/getEmployee', [AuthController::class, 'getEmployee']);
Route::get('/updateEmployee/{id}', [AuthController::class, 'updateEmployee']);

Route::delete('/deleteEmployee/{id}', [AuthController::class, 'deleteEmployee']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/depositMoney/{employeeId}', [AccountController::class, 'depositMoney'])->middleware('admin');
    Route::post('/withdrawMoney/{employeeId}', [AccountController::class, 'withdrawMoney'])->middleware('admin');
    Route::post('/addRefund/{employeeId}', [AccountController::class, 'addRefund'])->middleware('admin');
    Route::get('/getAccount/{employeeId}', [AccountController::class, 'getAmountOfEmployee']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/changeConstraint', [ConstraintController::class, 'changeConstraint'])->middleware('admin');
    Route::get('/getConstraint', [ConstraintController::class, 'getConstraint']);
});



Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/menu-items', [MenuItemController::class, 'createMenuItem'])->middleware('admin');
    Route::get('/menu-items', [MenuItemController::class, 'getAllMenuItems'])->middleware(['auth:sanctum']);
    Route::get('/menu-items-no-filter', [MenuItemController::class, 'getAllMenuItemsNoFilter'])->middleware(['auth:sanctum']);
    Route::post('/update-menu-items/{id}', [MenuItemController::class, 'updateMenuItem'])->middleware('admin');
    Route::post('/update-avail-menu-items/{id}', [MenuItemController::class, 'updateMenuItemAvail'])->middleware('admin');
    Route::post('/update-available-amount/{id}', [MenuItemController::class, 'updateAvailableAmount'])->middleware('admin');
    Route::post('/menu-itemsdelete/{id}', [MenuItemController::class, 'removeMenuItem'])->middleware('admin');
    Route::get('/search', [MenuItemController::class, 'searchMenuItems'])->middleware(['auth:sanctum']);
    Route::get('/menu-items/today', [MenuItemController::class, 'getTodayOrders'])->middleware('admin');
});




Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/create-department', [DepartmentController::class, 'create'])->middleware('admin');
    Route::get('/get-all-departments', [DepartmentController::class, 'getAll'])->middleware(['auth:sanctum']);
    Route::get('/get-all-paginated-departments', [DepartmentController::class, 'getAllPagination'])->middleware(['auth:sanctum']);
    Route::post('update-department/{id}', [DepartmentController::class, 'update'])->middleware('admin');
    Route::delete('/delete-department/{id}', [DepartmentController::class, 'deleteDepartment'])->middleware('admin');
    Route::get('/get-department-by-id', [DepartmentController::class, 'getById'])->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/order', [OrderController::class, 'placeOrder']);
    Route::post('/orders/{id}', [OrderController::class, 'updateOrder']);
    Route::get('/orders', [OrderController::class, 'getAllOrders'])->middleware('admin');
    Route::get('/dashboard/status', [OrderController::class, 'getDashboardStats'])->middleware('admin');
    Route::get('/report', [OrderController::class, 'getReportStats'])->middleware('admin');
    Route::get('/custom/report', [OrderController::class, 'getCustomReportStats'])->middleware('admin');
    Route::post('/orderstatuschange/{id}', [OrderController::class, 'updateOrder']);
    Route::get('/orders/{employee}', [OrderController::class, 'getOrdersByEmployee']);
    Route::get('/searchCoupon', [OrderController::class, 'searchByCoupon']);
});




Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/guestOrderStatus/{id}', [GuestOrderController::class, 'updateOrder']);
    Route::post('/guestOrder', [GuestOrderController::class, 'placeOrder']);
    Route::post('/guestOrderElectronic', [GuestOrderController::class, 'placeOrderElectronic']);
    Route::get('/getGuestOrders', [GuestOrderController::class, 'getAllOrders']);
       Route::get('/guestOrders/{employee}', [GuestOrderController::class, 'getGuestOrdersByEmployee']);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/placeDepartmentOrder', [DepartmentOrderController::class, 'placeOrder']);
    Route::get('/departmentOrders', [DepartmentOrderController::class, 'getAllOrders'])->middleware('admin');
    Route::get('/department-orders/{id}', [DepartmentOrderController::class, 'getDepartmentOrderById'])->middleware('admin');


});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/inventory', [InventoryEntryController::class, 'createInventoryEntryList'])->middleware('admin');
    Route::get('/getinventory/{entryId}', [InventoryEntryController::class, 'getInventoryDataById'])->middleware('admin');
    Route::get('/getallinventory', [InventoryEntryController::class, 'getAllInventoryData'])->middleware('admin');
    Route::get('/getinventoryremained', [InventoryEntryController::class, 'getAllInventory'])->middleware('admin');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/requestApproval', [InventoryRequestController::class, 'InventoryRequestApproval'])->middleware('admin');
    Route::post('/request', [InventoryRequestController::class, 'createInventoryList'])->middleware('admin');
    Route::get('/requestfetchinv', [InventoryRequestController::class, 'getAllInventoryRequests'])->middleware('admin');
    Route::get('requestbyid/{reqId}', [InventoryRequestController::class, 'getRequestById'])->middleware('admin');
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/stockrequest', [StockRequestController::class, 'createStockList'])->middleware('admin');
    Route::get('/requestfetch', [StockRequestController::class, 'getStockRequests'])->middleware('admin');
    Route::post('/stockapprove', [StockRequestController::class, 'approve'])->middleware('admin');


});
