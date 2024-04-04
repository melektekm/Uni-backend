<?php

namespace App\Http\Controllers;


use App\Models\Constraint;
use App\Models\Employee;
use App\Models\GuestOrder;
use App\Models\GuestOrderMenuItem;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class GuestOrderController extends Controller
{
    public function placeOrder(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'total_price' => 'required|numeric',
            'name' => 'nullable',
            'buyer_id' => 'required',
            'menu_items' => 'required|array|min:1',
            'menu_items.*.quantity' => 'required|integer',
            'menu_items.*.menu_item_id' => 'required|exists:menu_items,id',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {

            $couponCode = Str::random(8);
            $order = GuestOrder::create([
                'coupon_code' => $couponCode,
                'guest_name' => $request['name'] ?? 'የእንግዳ ተጠቃሚ',
                'buyer_id' => $request['buyer_id'],
                'total_price' => $request['total_price'],

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
                GuestOrderMenuItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity']
                ]);

            }
            Transaction::create([
                'account_id' => null,
                'type' => 'guestOrder',
                'amount' => $request['total_price'],
            ]);

            DB::commit();

            return response([
                'message' => 'ትእዛዝ በተሳካ ሁኔታ ቀርቧል',
                'menu' => $order,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response([
                'message' => 'ትዕዛዙን ማዘዝ አልተሳካም።',
                'error' => $e->getMessage(),
            ], 500);
        }


    }

    public function placeOrderElectronic(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'total_price' => 'required|numeric',
            'menu_items' => 'required|array|min:1',
            'menu_items.*.quantity' => 'required|integer',
            'menu_items.*.menu_item_id' => 'required|exists:menu_items,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()], 422);
        }

        $validateOrderResponse = $this->validateOrder($request);

        if ($validateOrderResponse->getStatusCode() != 200) {
            return $validateOrderResponse;
        }

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($request['employee_id']);
            $account = $employee->account;
            $guestName = $employee->name;

            if ($account->balance < $request['total_price']) {

                DB::rollback();
                return response([
                    'message' => 'ትዕዛዙን ማዘዝ አልተሳካም። በቂ ያልሆነ ገንዘብ',
                    'error' => 'ለኤሌክትሮኒክ ክፍያ በቂ ያልሆነ ቀሪ ሂሳብ',
                ], 400);
            }

            $account->balance -= $request['total_price'];
            $account->save();
            $couponCode = Str::random(8);
            $order = GuestOrder::create([
                'guest_name' => $guestName,
                'buyer_id' => $employee->id,
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
                GuestOrderMenuItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            Transaction::create([
                'account_id' => $account->id,
                'type' => 'guestOrder',
                'amount' => $request['total_price'],
            ]);

            DB::commit();

            return response([
                'message' => 'ትእዛዝ በተሳካ ሁኔታ ቀርቧል',
                'menu' => $order,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response([
                'message' => 'ትዕዛዙን ማዘዝ አልተሳካም።',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllOrders(Request $request)
    {
        $page = $request->input('per_page', 20);
        $timeRange = $request->input('time_range', 'today');
        $query = GuestOrder::query();

        switch ($timeRange) {
            case 'today':
                $query = $query->whereDate('created_at', Carbon::today());
                break;
            case 'this_week':
                $query = $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'this_month':
                $query = $query->whereMonth('created_at', Carbon::now()->month);
                break;
            default:
                break;
        }

        $query->orderBy('created_at', 'desc');

        $orders = $query->paginate($page);

        $orders->getCollection()->transform(function ($order) {
            return [
                'id' => $order->id,
                'date_human' => $order->created_at->diffForHumans(),
                'created_at' => Carbon::parse($order->created_at)->format('D, M d, Y h:i A'),
                'total_price' => $order->total_price,
                'guest_name' => $order->guest_name,
                'status' => $order->status,
                'coupon_code' => $order->coupon_code,
                'buyer_id' => $order->buyer_id,
                'ordered_items' => $order->menuItems->map(function ($menuItem) {
                    $menuItemModel = MenuItem::find($menuItem->menu_item_id);
                    $quantity = $menuItem->quantity;

                    return [
                        'name' => $menuItemModel ? $menuItemModel->name : 'N/A',
                        'price_for_guest' => $menuItemModel ? $menuItemModel->price_for_guest : 0,
                        'quantity' => $quantity,
                    ];
                })->toArray(),
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

        $order = GuestOrder::where('id', $id);
        $order = GuestOrder::find($id);

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

        $orderStats = GuestOrder::select(
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

        $ExpStats = Ingredient::select(
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('DATE_FORMAT(created_at, "%M") as month'),
            DB::raw('SUM(itemPrice) as total_expense')
        )
            ->whereYear('created_at', $selectedYear)
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
                'total_orders' => $orderStats[$num]->total_orders ?? 0,
                'total_revenue' => $orderStats[$num]->total_revenue ?? 0,
                'total_expense' => $ExpStats[$num]->total_expense ?? 0
            ];
        }


        return response()->json($response);

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
                    } else {
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


            $orderCountFromDB = GuestOrderMenuItem::whereHas('order', function ($query) use ($employeeId, $breakfastStartTime, $breakfastEndTime) {
                $query->where('buyer_id', $employeeId)
                    ->whereBetween('created_at', [$breakfastStartTime, $breakfastEndTime]);
            })
                ->whereIn('menu_item_id', function ($query) {
                    $query->select('id')->from('menu_items')->where('meal_type', 'breakfast');
                })
                ->sum('quantity');

            $orderCountFromRequest = array_sum(array_column($request->menu_items, 'quantity'));

            $totalOrderCount = $orderCountFromDB + $orderCountFromRequest;

            $maxBreakfastOrderConstraint = Constraint::where('constraint_name', 'GuestBreakfastOrderMaxAmount')->first();

            if (!$maxBreakfastOrderConstraint) {
                return response()->json(['message' => 'ከፍተኛውን የቁርስ ማዘዣ ገደብ ማምጣት አልተሳካም።'], 500);
            }

            $maxBreakfastOrder = $maxBreakfastOrderConstraint->max_num;

            if ($totalOrderCount > $maxBreakfastOrder) {
                $messages = [
                    0 => "You have reached the maximum breakfast order amount as a guest.", 
                    1 =>  'እንደ እንግዳ ሊያደርጉት የሚችሉትን የቁርስ የትዕዛዝ ገደብ (' . $maxBreakfastOrder . ') አልፈዋል።', 
                    2 => "ከም ጋሻ መጠን ዝለዓለ መጠን ቍርሲ በጺሕካ ኢኻ።", 
                    3 => "Akka keessummaatti hanga ajaja ciree ol'aanaa irra geesseetta.",
                    4 => "Marti ahaan waxaad gaadhay qadarka dalabka quraacda ee ugu badnaa.",
                
                  
                ];
        
                return response(['message' => $messages], 400);
       
            }

        } else if ($currentTime->between($lunchStartTime, $lunchEndTime)) {

            $menuItems = $request['menu_items'];
            $isLunchOrder = true;

            foreach ($menuItems as $menuItem) {
                $menuItemData = MenuItem::find($menuItem['menu_item_id']);

                if (!$menuItemData || ($menuItemData->meal_type !== 'lunch')) {
                    if (($menuItemData->meal_type == 'drink')) {
                        continue;
                    }
                    $isLunchOrder = false;
                    break;
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


            $orderCountFromDB = GuestOrderMenuItem::whereHas('order', function ($query) use ($employeeId, $lunchStartTime, $lunchEndTime) {
                $query->where('buyer_id', $employeeId)
                    ->whereBetween('created_at', [$lunchStartTime, $lunchEndTime]);
            })
                ->whereIn('menu_item_id', function ($query) {
                    $query->select('id')->from('menu_items')->where('meal_type', 'lunch');
                })
                ->sum('quantity');


            $orderCountFromRequest = array_sum(array_column($request->menu_items, 'quantity'));
            $totalOrderCount = $orderCountFromDB + $orderCountFromRequest;

            $maxLunchOrderConstraint = Constraint::where('constraint_name', 'GuestLunchOrderMaxAmount')->first();

            if (!$maxLunchOrderConstraint) {
                return response()->json(['message' => 'ከፍተኛውን የምሳ ማዘዣ ገደብ ማምጣት አልተሳካም።'], 500);
            }
            $maxLunchOrder = $maxLunchOrderConstraint->max_num;

            if ($totalOrderCount > $maxLunchOrder) {
                $messages = [
                    0 => "You have reached the maximum lunch order amount as a guest.", 
                    1 =>  'እንደ እንግዳ ሊያደርጉት የሚችሉትን የምሳ  የትዕዛዝ ገደብ (' . $maxLunchOrder . ') አልፈዋል።', 
                    2 => "ከም ጋሻ መጠን ዝለዓለ መጠን ናይ ምሳሕ ትእዛዝ በጺሕካ ኢኻ።", 
                    3 => "Akka keessummaatti hanga ajaja laaqanaa ol aanaa irra geesseetta.",
                    4 => "Marti ahaan waxaad gaartay qadarka ugu badan ee dalabka qadada.",
                
                  
                ];
        
                return response(['message' => $messages], 400);
            }


        } else {
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

    public function getGuestOrdersByEmployee($id)
    {
        $systemUser = \App\Models\Employee::where('id', $id)->first();

        if (!$systemUser) {
            return response([
                'error' => 'በተሰጠው መታወቂያ ምንም የተመዘገበ ሰራተኛ አልተገኘም።'
            ]);
        }

        $guestOrders = $systemUser->guestOrders()->whereDate('created_at', today())->orderBy('created_at', 'desc')->paginate(20);

        $guestOrderResources = \App\Http\Resources\GuestOrder::collection($guestOrders);

        $guestOrderResources->transform(function ($resource) {
            $resource->created_at_for_humans = $resource->created_at->diffForHumans();
            return $resource;
        });

        return $guestOrderResources;
    }


}
