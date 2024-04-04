<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
MenuItem::unguard();
class MenuItemController extends Controller
{
    public function createMenuItem(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:1024',
            'price_for_guest' => 'required|numeric',
            'price_for_employee' => 'required|numeric',
            'meal_type' => 'required',
            'is_fasting' => 'required|boolean',
            'is_available' => 'required|boolean',
            'available_amount' => 'nullable|numeric',

        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }


        $mealType = $request->input('meal_type');
        $isDrink = ($mealType === 'drink');
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');

            }
            else{
                if($isDrink){
                    $imagePath ='images/drink1.jpg';

                }
                else{
                    $imagePath ='images/defaultFood.jpg';
                }

            }
            $isAvailable = $request->input('is_available', false);
            $availableAmount = $request->input('available_amount', 0);

            if (!is_null($availableAmount) && $availableAmount > 0) {
                $isAvailable = true;
            }

            $menuItem = MenuItem::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'image_url' => $imagePath,
                'price_for_guest' => $request->input('price_for_guest'),
                'price_for_employee' => $request->input('price_for_employee'),
                'meal_type' => $mealType,
                'is_fasting' => $request->input('is_fasting'),
                'is_available' => $isAvailable,
                'available_amount' => $availableAmount,
                'is_drink'  =>  $isDrink,
            ]);


        return response([
            'message' => "በተሳካ ሁኔታ ምግብ አክለዋል",
            'menu' => $menuItem,
        ], 200);
    }
    public function updateMenuItem(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'price_for_guest' => 'required|numeric',
            'price_for_employee' => 'required|numeric',
            'meal_type' => 'required',
            'is_fasting' => 'required|boolean',
            'is_available' => 'required|boolean',
            'available_amount' => 'nullable|numeric',

        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }


        $menuItem = MenuItem::findOrFail($id);


        $menuItem->name = $request->input('name');
        $menuItem->description = $request->input('description');
        $menuItem->price_for_guest = $request->input('price_for_guest');
        $menuItem->price_for_employee = $request->input('price_for_employee');
        $menuItem->meal_type = $request->input('meal_type');
        $menuItem->is_fasting = $request->input('is_fasting');
        $isAvailable = $request->input('is_available', false);
        $availableAmount = $request->input('available_amount', 0);
        $mealType = $request->input('meal_type');
        $menuItem->is_drink = ($mealType === 'drink');

        if (!is_null($availableAmount) && $availableAmount > 0) {
            $isAvailable = true;
        }else{
            $isAvailable = false;
        }

        $menuItem->is_available = $isAvailable;
        $menuItem->available_amount = $availableAmount;
        if ($request->hasFile('image')) {

            $imagePath = $request->file('image')->store('images', 'public');
            $menuItem->image_url = $imagePath;
        }


        $menuItem->save();

        return response([
            'message' => "
ምግቡን በተሳካ ሁኔታ አዘምኗል",
            'menu' => $menuItem,
        ], 200);
    }
    public function   updateAvailableAmount(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'available_amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }

        $menuItem = MenuItem::findOrFail($id);


        $menuItem->available_amount = $request->input('available_amount');
        $availableAmount = $request->input('available_amount', 0);

     if ($availableAmount > 0) {
                $menuItem->is_available = true;
       } else {
                  $menuItem->is_available = false;
         }


        $menuItem->save();

        return response([
            'message' => "ምግቡን በተሳካ ሁኔታ አዘምኗል",
            'menu' => $menuItem,
        ], 200);
    }
    public function getAllMenuItemsNoFilter()
    {

            $menuItems = MenuItem::paginate(9);

       
    foreach ($menuItems as $menuItem) {
       
     
    }

    return response()->json($menuItems);
    }
    public function getAllMenuItems()
{

    $menuItems = MenuItem::where('is_available', 1)->paginate(9);
     

    return response()->json($menuItems);
}
    public function removeMenuItem($id)
    {
        try {
            $menuItem = MenuItem::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json(['error' => 'ምግብ አልተገኘም'], 404);
        }



        $menuItem->delete();


        return response()->json(['message' => 'ምግብ ተሰርዟል']);
    }
    public function getTodayOrders(Request $request)
    {
        $timeRange = $request->input('time_range');

        $startDate = Carbon::today();
        $endDate = Carbon::today();

        switch ($timeRange) {
            case 'today':
                $startDate = Carbon::today()->startOfDay();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'this_week':
                $startDate = Carbon::today()->startOfWeek();
                $endDate = Carbon::today()->endOfWeek();
                break;
            case 'this_month':
                $startDate = Carbon::today()->startOfMonth();
                $endDate = Carbon::today()->endOfMonth();
                break;
            case 'all':
                $startDate = null;
                $endDate = null;
                break;
        }

        $menuItems = MenuItem::where(function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereHas('orders', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('orders.created_at', [$startDate, $endDate]);
                })->orWhereHas('guestOrders', function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('guest_orders.created_at', [$startDate, $endDate]);
                });
            }
        })->with(['orders' => function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('orders.created_at', [$startDate, $endDate]);
            }
        }, 'guestOrders' => function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('guest_orders.created_at', [$startDate, $endDate]);
            }
        }])->get();
        $menuItemQuantities = [];
        foreach ($menuItems as $menuItem) {
            $totalQuantity = 0;
            foreach ($menuItem->orders as $order) {
                $totalQuantity += $order->pivot->quantity;
            }
            foreach ($menuItem->guestOrders as $guestOrder) {
                $totalQuantity += $guestOrder->pivot->quantity;
            }
            $menuItemQuantities[$menuItem->name] = $totalQuantity;
        }

        return response()->json($menuItemQuantities);
    }
    public function searchMenuItems(Request $request)
{
    $term = $request->term;

    $results = (new MenuItem())->searchFuzzy(MenuItem::query(), $term)
        ->where('is_available', 1)
        ->get();  
        $rankedResults = $this->rankResults($results, $term);
        return response()->json($rankedResults);

}
 public function rankResults($results, $searchTerm) {
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

}









