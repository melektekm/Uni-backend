<?php

namespace App\Http\Controllers;

use App\Models\Constraint;
use App\Models\DepartmentOrder;
use App\Models\Employee;
use App\Models\GuestOrder;
use App\Models\Ingredient;
use App\Models\InventoryRequestEntry;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderMenuItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class OrderController extends Controller
{
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'total_price' => 'required|numeric',
            'employee_id' => 'required|exists:employees,id',
            'menu_items' => 'required|array|min:1',
            'menu_items.*.quantity' => 'required|integer',
            'menu_items.*.menu_item_id' => 'required|exists:menu_items,id',
        ]);

        $validateOrderResponse = $this->validateOrder($request);

        if ($validateOrderResponse->getStatusCode() != 200) {
            return $validateOrderResponse;
        }

        if ($validator->fails()) {
            return response([
                'message' => 'ልክ ያልሆነ መረጃ ገብቷል።',
                'errors' => $validator->errors(),
            ], 422);
        } else {

            DB::beginTransaction();

            try {
                $employee = Employee::findOrFail($request['employee_id']);
                if ( $employee  && $employee->status) {
                    $messages = [
                        0 => "Your account has been banned. Please reach out to the authorities to request reinstatement", 
                        1 => "ይህ አካውንት ስለታገደ ትእዛዝ መፈጸም አይችሉም። አካውንቱን ለማስከፈት የሚመለከተውን አካል ያነጋግሩ ።", 
                        2 => "ጸብጻብካ ተኣጊዱ እዩ ። ናብ ሃገሮም ኪምለሱ ንምሕታት በጃኻ ናብ ሰበ ስልጣን ሕተቶም ", 
                        3 => "Akkaawuntii keessan ugguramee jira. Hojiitti akka deebi'an gaafachuuf aanga'oota qunnamaa",
                        4 => "Koontadaada waa la mamnuucay Fadlan la xiriir maamulka si aad u codsato dib u soo celinta",
                    
                      
                    ];
                
                    return response(['message' => $messages], 400);

                }
                $account = $employee->account;

                if ($account->balance < $request['total_price']) {
                    DB::rollBack();
                    return response([
                        'message' => 'በቂ ያልሆነ ገንዘብ',
                    ], 400);
                }

                $account->balance -= $request['total_price'];
                $account->save();

                Transaction::create([
                    'account_id' => $account->id,
                    'type' => 'order',
                    'amount' => $request['total_price'],
                ]);

                $couponCode = Str::random(8);
                $order = Order::create([
                    'employee_id' => $request['employee_id'],
                    'total_price' => $request['total_price'],
                    'coupon_code' => $couponCode,
                ]);

                foreach ($request['menu_items'] as $item) {
                    $menuItem = MenuItem::find($item['menu_item_id']);


                    $availableAmount = $menuItem->available_amount;
                    $requestedQuantity = $item['quantity'];

                    if ($availableAmount < $requestedQuantity) {

                        DB::rollBack();
                        return response([
                            'message' => 'የተጠየቁ ምግቦች ለትዕዛዝ አይገኙም።',
                        ], 400);

                    } elseif ($availableAmount === $requestedQuantity) {
                        $menuItem->is_available = 0;
                    }


                    $menuItem->available_amount -= $requestedQuantity;
                    $menuItem->save();

                    OrderMenuItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $item['quantity'],
                    ]);

                }

                DB::commit();

                return response([
                    'message' => 'ትእዛዝ በተሳካ ሁኔታ ቀርቧል',
                    'menu' => $order,
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response([
                    'message' => 'ትዕዛዙን ማዘዝ አልተሳካም።',
                    'error' => $e->getMessage(),
                ], 500);
            }

        }
    }


    public function validateOrder(Request $request)
    {
        $isCafeOpen = !Constraint::where('constraint_name', 'OrderOpened')->first()->isclosed;

        if (!$isCafeOpen) {
          
            $messages = [
                0 => "The cafe is closed", 
                1 => "ካፌው አሁን ተዘግቷል።", 
                2 => "ካፊተርያ ተዓጽወ", 
                3 => "Kaafeen cufaadha",
                4 => "Maqaaxiyaha ayaa xiran",
            
              
            ];
        
            return response(['message' => $messages], 400);

        }

        $menuItemsNotAvailable = [];

        foreach ($request['menu_items'] as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            if ($menuItem && (!$menuItem->is_available || $item['quantity'] > $menuItem->available_amount)) {
                array_push($menuItemsNotAvailable, $menuItem->name);
            }
        }

        if (!empty($menuItemsNotAvailable)) {
            return response([
                'message' => "የተጠየቁ ምግቦች ለትዕዛዝ አይገኙም።",
                'menu_items_not_available' => $menuItemsNotAvailable
            ], 400);
        }

        $menuItems = $request['menu_items'];
        $allItemsAreDrinks = true;
        foreach ($menuItems as $menuItem) {
            $menuItemData = MenuItem::find($menuItem['menu_item_id']);
            if ($menuItemData->meal_type != 'drink') {
                $allItemsAreDrinks = false;
                break;
            }
        }

        if ($allItemsAreDrinks) {
            return response([
                'message' => "ሁሉም መጠጦች ይገኛሉ",
            ], 200);
        }


        $currentTime = Carbon::now();
        $breakfastStartTime = Constraint::where('constraint_name', 'BreakfastOrderTime')->first()->start_time;
        $breakfastEndTime = Constraint::where('constraint_name', 'BreakfastOrderTime')->first()->end_time;
        $lunchStartTime = Constraint::where('constraint_name', 'LunchOrderTime')->first()->start_time;
        $lunchEndTime = Constraint::where('constraint_name', 'LunchOrderTime')->first()->end_time;
        $breakfastStartTime = Carbon::parse($breakfastStartTime);
        $breakfastEndTime = Carbon::parse($breakfastEndTime);
        $lunchStartTime = Carbon::parse($lunchStartTime);
        $lunchEndTime = Carbon::parse($lunchEndTime);

        if ($currentTime->between($breakfastStartTime, $breakfastEndTime)) {

            $menuItems = $request['menu_items'];
            $isBreakfastOrder = true;

            foreach ($menuItems as $menuItem) {
                $menuItemData = MenuItem::find($menuItem['menu_item_id']);

                if (!$menuItemData || ($menuItemData->meal_type !== 'breakfast')) {
                    if (($menuItemData->meal_type == 'drink')) {
                        continue;
                    }
                    else {
                        $isBreakfastOrder = false;
                        break;
                    }
                }
            }

            if (!$isBreakfastOrder) {
                $messages = [
                    0 => "It's breakfast order time. You can only order breakfast-type meals.", 
                    1 => "አሁን የቁርስ ሰአት ነው። የቁርስ ምግቦችን ብቻ ነው ማዘዝ የሚችሉት።", 
                    2 => "ግዜ ቝርሲ እዩ ። ዓይነት ቍርሲ ጥራይ ኢኻ ኽትእዝዝ እትኽእል።", 
                    3 => "Yeroon ajaja ciree ti, yoou nyaata gosa ciree qofa ajajuu dandeessa",
                    4 => "Waa wakhtiga dalabka ee brekfast, yoou waxa uu dalban karaa oo kaliya cuntooyinka nooca brekfast",
                
                  
                ];
            
                return response(['message' => $messages], 400);
            }

            $employeeId = $request['employee_id'];


            $orderCountFromDB = OrderMenuItem::whereHas('order', function ($query) use ($employeeId, $breakfastStartTime, $breakfastEndTime) {
                $query->where('employee_id', $employeeId)
                    ->whereBetween('created_at', [$breakfastStartTime, $breakfastEndTime]);
            })
                ->whereIn('menu_item_id', function ($query) {
                    $query->select('id')->from('menu_items')->where('meal_type', 'breakfast');
                })
                ->sum('quantity');

            $orderCountFromRequest = array_sum(array_column($request->menu_items, 'quantity'));

            $totalOrderCount = $orderCountFromDB + $orderCountFromRequest;

            $maxBreakfastOrderConstraint = Constraint::where('constraint_name', 'EmployeeBreakfastOrderMaxAmount')->first();

            if (!$maxBreakfastOrderConstraint) {
                return response()->json(['message' => 'ከፍተኛውን የቁርስ ማዘዣ ገደብ ማምጣት አልተሳካም።'], 500);
            }

            $maxBreakfastOrder = $maxBreakfastOrderConstraint->max_num;


            if ($totalOrderCount > $maxBreakfastOrder) {
                $messages = [
                    0 => "You have reached the maximum order amount as an employee. Please try placing your order as a guest", 
                    1 => "ከፍተኛው የቁርስ ትዕዛዝ መጠን ('.$maxBreakfastOrder.') ታልፏል። እንደ እንግዳ ምግብ ለማዘዝ ይሞክሩ", 
                    2 => "ከም ሰራሕተኛ መጠን ዝለዓለ መጠን ትእዛዝ በጺሕካ ኢኻ ። በጃኻ ትእዛዝካ ኸም ጋሻ ጌርካ ኸተቐምጦ ፈትን", 
                    3 => "Akka hojjetaatti hanga ajaja guddaa irra geesseetta. Mee ajaja keessan akka keessummaatti kennuu yaalaa",
                    4 => "Waxaad gaadhay qadarka dalbashada ugu badan ee shaqaale ahaan. Fadlan isku day inaad dalabkaaga soo gudbiso marti ahaan",
                
                  
                ];
            
                return response(['message' => $messages], 400);

            }

        }
        else if ($currentTime->between($lunchStartTime, $lunchEndTime)) {
            $menuItems = $request['menu_items'];
            $isLunchOrder = true;
            foreach ($menuItems as $menuItem) {
                $menuItemData = MenuItem::find($menuItem['menu_item_id']);

                if (!$menuItemData || ($menuItemData->meal_type !== 'lunch')) {
                    if (($menuItemData->meal_type == 'drink')) {
                        continue;
                    }
                    else{
                    $isLunchOrder = false;
                    break;
                    }
                }
            }

            if (!$isLunchOrder) {
                $messages = [
                    0 => "It's lunch order time. You can only order breakfast-type meals.", 
                    1 => "አሁን የምሳ ሰአት ነው። የምሳ ምግቦችን ብቻ ነው ማዘዝ የሚችሉት።", 
                    2 => "እዋኑ ናይ ምሳሕ ትእዛዝ እዩ። ዓይነት ቍርሲ ጥራይ ኢኻ ኽትእዝዝ እትኽእል።", 
                    3 => "Yeroon ajaja laaqanaati. Nyaata gosa ciree qofa ajajuu dandeessu.",
                    4 => "Waa xiligii qadada. Waxaad dalban kartaa oo kaliya cuntooyinka nooca quraacda ah.",
                
                  
                ];
            
                return response(['message' => $messages], 400);

            }

            $employeeId = $request['employee_id'];


            $orderCountFromDB = OrderMenuItem::whereHas('order', function ($query) use ($employeeId, $lunchStartTime, $lunchEndTime) {
                $query->where('employee_id', $employeeId)
                    ->whereBetween('created_at', [$lunchStartTime, $lunchEndTime]);
            })
                ->whereIn('menu_item_id', function ($query) {
                    $query->select('id')->from('menu_items')->where('meal_type', 'lunch');
                })
                ->sum('quantity');


            $orderCountFromRequest = array_sum(array_column($request->menu_items, 'quantity'));

            $totalOrderCount = $orderCountFromDB + $orderCountFromRequest;

            $maxLunchOrderConstraint = Constraint::where('constraint_name', 'EmployeeLunchOrderMaxAmount')->first();

            if (!$maxLunchOrderConstraint) {
                return response()->json(['message' => 'ከፍተኛውን የምሳ ማዘዣ ገደብ ማምጣት አልተሳካም።'], 500);
            }

            $maxLunchOrder = $maxLunchOrderConstraint->max_num;

            if ($totalOrderCount > $maxLunchOrder) {

                $messages = [
                    0 => "You have reached the maximum lunch order amount as an employee. Please try placing your order as a guest", 
                    1 => "ከፍተኛው የምሳ ትዕዛዝ መጠን ('.$maxLunchOrder.') ታልፏል። እንደ እንግዳ ምግብ ለማዘዝ ይሞክሩ') ታልፏል። እንደ እንግዳ ምግብ ለማዘዝ ይሞክሩ", 
                    2 => "ከም ሰራሕተኛ መጠን ዝለዓለ መጠን ትእዛዝ በጺሕካ ኢኻ ። በጃኻ ትእዛዝካ ኸም ጋሻ ጌርካ ኸተቐምጦ ፈትን", 
                    3 => "Akka hojjetaatti hanga ajaja guddaa irra geesseetta. Mee ajaja keessan akka keessummaatti kennuu yaalaa",
                    4 => "Waxaad gaadhay qadarka dalbashada ugu badan ee shaqaale ahaan. Fadlan isku day inaad dalabkaaga soo gudbiso marti ahaan",
                
                  
                ];
            
                return response(['message' => $messages], 400);


            }

        }
        else {
            $menuItems = $request['menu_items'];
            foreach ($menuItems as $menuItem) {
                $menuItemData = MenuItem::find($menuItem['menu_item_id']);

                if (($menuItemData->meal_type == 'drink')) {
                    continue;
                }
                $messages = [
                    0 => "The cafe is currently accepting only drink orders.", 
                    1 => "ካፌው በዚህ ሰአት ከመጠጥ የሜኑ አይነቶች ዉጪ ትዕዛዝ  አይቀበልም", 
                    2 => "ኣብዚ እዋን እዚ እቲ ሻሂ ናይ መስተ ትእዛዝ ጥራይ ኢዩ ዝቕበል ዘሎ።", 
                    3 => "Kaafeen kun yeroo ammaa ajaja dhugaatii qofa fudhachaa jira.",
                    4 => "Kafeega ayaa hadda aqbalaya dalabaadka cabitaanka oo kaliya.",
                
                  
                ];
            
                return response(['message' => $messages], 400);
              

            }

        }

        return response([
            'message' => "ሁሉም ምግቦች ይገኛሉ",
        ], 200);
    }

    public function searchByCoupon(Request $request)
    {
        $searchTerm = $request->input('coupon_code');
        $orders = Order::with(['employee', 'menuItems'])
            ->where('coupon_code', 'LIKE', "%{$searchTerm}%")
            ->get()
            ->transform(function ($order) {
                $employee = Employee::find($order->employee->id);
                return [
                    'id' => $order->id,
                    'employee_name' => $employee->name,
                    'total_price' => $order->total_price,
                    'coupon_code' => $order->coupon_code,
                    'status' => $order->status,
                    'created_at' => Carbon::parse($order->created_at)->format('M d, Y h:i A'),
                    'menu_items' => $order->menuItems->map(function ($menuItem) {
                        return [
                            'name' => $menuItem->name,
                            'mealType' => $menuItem->meal_type,
                            'quantity' => $menuItem->pivot->quantity,
                        ];
                    }),
                ];
            });

        return response()->json($orders);
    }


    public function getAllOrders(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $timeRange = $request->input('time_range', 'today');
        $orders = Order::with(['employee', 'menuItems']);

        switch ($timeRange) {
            case 'today':
                $orders = $orders->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                $orders = $orders->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $orders = $orders->whereMonth('created_at', Carbon::now()->month);
                break;
            default:
                break;
        }

        $orders = $orders->latest('created_at')->paginate($perPage);

        $orders->getCollection()->transform(function ($order) {
            $employee = Employee::where('id', $order->employee->id)->first();
            return [
                'id' => $order->id,
                'employee_name' => $employee->name,
                'total_price' => $order->total_price,
                'coupon_code' => $order->coupon_code,
                'status' => $order->status,
                'created_at' => Carbon::parse($order->created_at)->format('M d, Y h:i A'),
                'menu_items' => $order->menuItems->map(function ($menuItem) {
                    return [
                        'name' => $menuItem->name,
                        'mealType' => $menuItem->meal_type,
                        'quantity' => $menuItem->pivot->quantity,
                    ];
                }),
            ];
        });

        return response()->json($orders);
    }

    public function updateOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::where('id', $id);
        $order = Order::find($id);

        if (!$order) {
            return response([
                'message' => 'ትዕዛዝ አልተገኘም።'
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response([
            'message' => 'የትዕዛዝ ሁኔታ በተሳካ ሁኔታ ዘምኗል'
        ], 200);
    }

    public function getOrderStats(Request $request)
    {
        $timeRange = $request->input('time_range');
        $query = Order::query();

        switch ($timeRange) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month);
                break;
        }

        $orderCount = $query->count();
        $totalRevenue = $query->sum('total_price');

        return response()->json([
            'order_count' => $orderCount,
            'total_revenue' => $totalRevenue,
        ]);

    }

    public function getDashboardStats(Request $request)
    {
        $timeRange = $request->input('time_range');

        $cashierId = Employee::where('role', "cashier")->first();
        $cashierId = $cashierId->id;

        $startTime = null;
        $endTime = null;

        $employeeOrderQuery = Order::query();
        $guestOrderQuery = GuestOrder::query();
        $depositQuery = Transaction::query()->where('type', 'deposit');
        $refundQuery = Transaction::query()->where('type', 'refund');
        $withdrawalQuery = Transaction::query()->where('type', 'withdrawal');
        $expenseQuery = InventoryRequestEntry::query();
        $departmentOrderQuery = DepartmentOrder::query();

        switch ($timeRange) {
            case 'today':
                 $startTime = Carbon::now();
                 $endTime = Carbon::now();
                $employeeOrderQuery->whereDate('created_at', Carbon::today());
                $guestOrderQuery->whereDate('created_at', Carbon::today());
                $depositQuery->whereDate('created_at', Carbon::today());
                $refundQuery->whereDate('created_at', Carbon::today());
                $withdrawalQuery->whereDate('created_at', Carbon::today());
                $expenseQuery->whereDate('created_at', Carbon::today());
                $departmentOrderQuery->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                 $startTime = Carbon::now()->startOfWeek();
                   $endTime = Carbon::now()->endOfWeek();
                $employeeOrderQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $guestOrderQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $depositQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $expenseQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $refundQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $withdrawalQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                $departmentOrderQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $startTime = Carbon::now()->startOfMonth();
                 $endTime = Carbon::now()->endOfMonth();
                $employeeOrderQuery->whereMonth('created_at', Carbon::now()->month);
                $guestOrderQuery->whereMonth('created_at', Carbon::now()->month);
                $depositQuery->whereMonth('created_at', Carbon::now()->month);
                $expenseQuery->whereMonth('created_at', Carbon::now()->month);
                $departmentOrderQuery->whereMonth('created_at', Carbon::now()->month);
                $refundQuery->whereMonth('created_at', Carbon::now()->month);
                $withdrawalQuery->whereMonth('created_at', Carbon::now()->month);
                break;
        }

        $guestOrderQueryEmployee = clone $guestOrderQuery;
        $guestOrderQueryCashier = clone $guestOrderQuery;

        $guestOrderCountEmployee = $guestOrderQueryEmployee->where('buyer_id', '<>', $cashierId)->count();
        $guestOrderCountCashier = $guestOrderQueryCashier->where('buyer_id', $cashierId)->count();

        $guestRevenueEmployee = $guestOrderQueryEmployee->where('buyer_id', '<>', $cashierId)->sum('total_price');
        $guestRevenueCashier = $guestOrderQueryCashier->where('buyer_id', $cashierId)->sum('total_price');

        $employeeOrderCount = $employeeOrderQuery->count();
        $employeeRevenue = $employeeOrderQuery->sum('total_price');

        $departmentOrderCount = $departmentOrderQuery->count();
        $departmentRevenue = $departmentOrderQuery
            ->get()
            ->sum(function ($row) {
                return ($row->lunch_price_per_person * $row->number_of_people * $row->number_of_days) +
                    ($row->refreshment_price_per_person * $row->refreshment_per_day* $row->number_of_people * $row->number_of_days);
            });

        $totalDeposit = $depositQuery->sum('amount') ;
        $cashReceived = $guestOrderQuery->where('buyer_id', $cashierId)->sum('total_price') + $totalDeposit + $departmentRevenue;
        $totalExpense = $expenseQuery->where('request_status', 'approved')
            ->get()
            ->sum(function ($row) {
                return $row->total_price_request - ($row->returned_amount ?? 0);
            });


        return response()->json([
             'start_time' => $startTime,
             'end_time' => $endTime,
            'employee_order_count' => $employeeOrderCount,
            'guest_order_count_employee' => $guestOrderCountEmployee,
            'guest_order_count_cashier' => $guestOrderCountCashier,
            'employee_revenue' => $employeeRevenue,
            'guest_revenue_employee' => $guestRevenueEmployee,
            'guest_revenue_cashier' => $guestRevenueCashier,
            'department_order_count' => $departmentOrderCount,
            'department_revenue' => $departmentRevenue,
            'total_deposit' => $totalDeposit,
            'refund_amount' => $refundQuery->sum('amount'),
            'withdrawal_amount' => $withdrawalQuery->sum('amount'),
            'cash_received' => $cashReceived,
            'total_expense' => $totalExpense,
        ]);
    }

    public function getReportStats(Request $request)
    {
        $selectedYear = $request->selected_year;

        $months = array(
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        );

        $orderStats = Order::select(
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('DATE_FORMAT(created_at, "%M") as month'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total_price) as total_revenue')
        )
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month_num', 'month')
            ->get()
            ->keyBy('month_num');

        foreach ($months as $num => $name) {
            if (!$orderStats->has($num)) {
                $orderStats->put($num, (object)['month' => $name, 'total_orders' => 0, 'total_revenue' => 0]);
            }
        }

        $orderStats = $orderStats->sortBy(function ($item, $key) use ($months) {
            return array_search($item->month, $months);
        });


        $guestOrderStats = GuestOrder::select(
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('DATE_FORMAT(created_at, "%M") as month'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total_price) as total_revenue')
        )
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month_num', 'month')
            ->get()
            ->keyBy('month_num');

        foreach ($months as $num => $name) {
            if (!$guestOrderStats->has($num)) {
                $guestOrderStats->put($num, (object)['month' => $name, 'total_orders' => 0, 'total_revenue' => 0]);
            }
        }

        $guestOrderStats = $guestOrderStats->sortBy(function ($item, $key) use ($months) {
            return array_search($item->month, $months);
        });


        $DeptStats = DepartmentOrder::select(
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('DATE_FORMAT(created_at, "%M") as month'),
            DB::raw('COUNT(*) as department_count'),
            DB::raw('SUM((lunch_price_per_person * number_of_people * number_of_days) + (refreshment_price_per_person * refreshment_per_day * number_of_people * number_of_days)) as department_total_price')
        )
            ->whereYear('created_at', $selectedYear)
            ->groupBy('month_num', 'month')
            ->get()
            ->keyBy('month_num');

        foreach ($months as $num => $name) {
            if (!$DeptStats->has($num)) {
                $DeptStats->put($num, (object)['month' => $name, 'department_count' => 0, 'department_total_price' => 0]);
            }
        }

        $DeptStats = $DeptStats->sortBy(function ($item, $key) use ($months) {
            return array_search($item->month, $months);
        });
        $refundStats = Transaction::select(
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('DATE_FORMAT(created_at, "%M") as month'),
            DB::raw('SUM(amount) as refund_amount')
        )
            ->whereYear('created_at', $selectedYear)
            ->where('type', 'refund')
            ->groupBy('month_num', 'month')
            ->get()
            ->keyBy('month_num');

        foreach ($months as $num => $name) {
            if (!$refundStats->has($num)) {
                $refundStats->put($num, (object)['month' => $name, 'refund_amount' => 0]);
            }
        }

        $refundStats = $refundStats->sortBy(function ($item, $key) use ($months) {
            return array_search($item->month, $months);
        });





        $ExpStats = InventoryRequestEntry::select(
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('DATE_FORMAT(created_at, "%M") as month'),
            DB::raw('SUM(total_price_request - COALESCE(returned_amount, 0)) as total_expense')
        )
            ->whereYear('created_at', $selectedYear)
            ->where('request_status', 'approved')
            ->groupBy('month_num', 'month')
            ->get()
            ->keyBy('month_num');

        foreach ($months as $num => $name) {
            if (!$ExpStats->has($num)) {
                $ExpStats->put($num, (object)['month' => $name, 'total_expense' => 0]);
            }
        }

        $ExpStats = $ExpStats->sortBy(function ($item, $key) use ($months) {
            return array_search($item->month, $months);
        });

        $response = [];
        foreach ($months as $num => $name) {
            $response[] = [
                'month' => $name,
                'employee_total_orders' => $orderStats[$num]->total_orders ?? 0,
                'guest_total_orders' => $guestOrderStats[$num]->total_orders ?? 0,
                'employee_total_revenue' => $orderStats[$num]->total_revenue ?? 0,
                'guest_total_revenue' => $guestOrderStats[$num]->total_revenue ?? 0,
                'total_expense' => $ExpStats[$num]->total_expense ?? 0,
                'department_count' => $DeptStats[$num]->department_count ?? 0,
                'refund_amount' => $refundStats[$num]->refund_amount ?? 0,
                'department_total_price' => $DeptStats[$num]->department_total_price ?? 0

            ];
        }


        return response()->json($response);

    }
    public function getCustomReportStats(Request $request)
    {
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date)->addDay()->subMinute();

        $orderStats = Order::select(
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total_price) as total_revenue')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();

        $guestOrderStats = GuestOrder::select(
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total_price) as total_revenue')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();

        $DeptStats = DepartmentOrder::select(
            DB::raw('COUNT(*) as department_count'),
            DB::raw('SUM((lunch_price_per_person * number_of_people * number_of_days) + (refreshment_price_per_person * refreshment_per_day * number_of_people * number_of_days)) as department_total_price')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();

        $ExpStats = InventoryRequestEntry::select(
            DB::raw('SUM(total_price_request - COALESCE(returned_amount, 0)) as total_expense')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('request_status', 'approved')
            ->first();

        $refundStats = Transaction::select(
            DB::raw('SUM(amount) as refund_amount')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('type', 'refund')
            ->first();

        $response = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'employee_total_orders' => $orderStats->total_orders ?? 0,
            'guest_total_orders' => $guestOrderStats->total_orders ?? 0,
            'employee_total_revenue' => $orderStats->total_revenue ?? 0,
            'guest_total_revenue' => $guestOrderStats->total_revenue ?? 0,
            'total_expense' => $ExpStats->total_expense ?? 0,
            'department_count' => $DeptStats->department_count ?? 0,
            'refund_amount' => $refundStats->refund_amount ?? 0,
            'department_total_price' => $DeptStats->department_total_price ?? 0
        ];

        return response()->json($response);
    }

    public function getOrdersByEmployee($id)
    {
        $systemUser = \App\Models\Employee::where('id', $id)->first();

        if (!$systemUser) {
            return response([
                'error' => 'በተሰጠው መታወቂያ ምንም የተመዘገበ ሰራተኛ አልተገኘም።'
            ]);
        }

        $orders = $systemUser->orders()->whereDate('created_at', today())->orderBy('created_at', 'desc')->paginate(10);

        $orderResources = \App\Http\Resources\Order::collection($orders);

        $orderResources->transform(function ($resource) {
            $resource->created_at_for_humans = $resource->created_at->diffForHumans();
            return $resource;
        });

        return $orderResources;
    }




}
