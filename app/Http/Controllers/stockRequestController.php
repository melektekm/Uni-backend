<?php

namespace App\Http\Controllers;

use App\Models\InventoryEntry;
use App\Models\StockRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class stockRequestController extends Controller
{
    public function createStockList(Request $request)
    {
        $lastRequest = StockRequest::latest('id')->first();


        if ($lastRequest && $lastRequest->approved_by === null) {
            return response([
                'message' => "Previous request was not approved",
            ], 422);
        }
        $lastGroup = StockRequest::max('group_id');
        $groupCounter = $lastGroup !== null ? $lastGroup + 1 : 1;

        $validator = Validator::make($request->all(), [
            'items.*.id' => 'required',
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric',
            'items.*.measured_in' => 'required',
            'items.*.requested_by' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }

        $inventoryItems = $request->input('items');;

        // Assuming you have an "InventoryItem" model
        $savedItems = [];
        foreach ($inventoryItems as $item) {
            $inventoryReq = StockRequest::create([
                'item_id'=>$item['id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'measured_in' => $item['measured_in'],
                'requested_by' => $item['requested_by'],
                'approved_by' => null,
                'group_id' => $groupCounter,
            ]);
            $savedItems[] = $inventoryReq;
        }

        return response([
            'message' => "Successfully added items",
            'requests' => $savedItems,
        ], 200);
    }




    public function getStockList(Request $request)
{
    $perPage = $request->input('per_page', 1); // You can specify the number of items per page

    // Assuming you have an "InventoryItem" model
    $groups = StockRequest::select('group_id')
        ->groupBy('group_id')
        ->orderBy('group_id', 'desc')
        ->paginate($perPage);

    $result = [];

    foreach ($groups as $group) {
        $items = StockRequest::where('group_id', $group->group_id)->get();
        $result[] = [
            'group_id' => $group->group_id,
            'items' => $items,
        ];
    }

    return response()->json([
        'groups' => $result,
        'pagination' => [
            'total' => $groups->total(),
            'per_page' => $groups->perPage(),
            'current_page' => $groups->currentPage(),
            'last_page' => $groups->lastPage(),
            'from' => $groups->firstItem(),
            'to' => $groups->lastItem(),
        ],
    ]);
}
public function getStockRequests(Request $request)
{
    $perPage = $request->input('per_page', 1);

    // Group the results by group_id
    $stockRequests = StockRequest::select([
        'id',
        'item_id',
        'name',
        'quantity',
        'created_at',
        'measured_in',
        'requested_by',
        'approved_by',
        'group_id',
    ])->orderBy('group_id', 'desc')->get()->groupBy('group_id');

    // Get the current page from the request
    $currentPage = $request->input('page', 1);

    // Get the keys (group_ids) from the grouped results
    $groupIds = $stockRequests->keys();

    // Calculate the total number of pages
    $totalPages = ceil(count($groupIds) / $perPage);

    // Slice the group_ids based on the current page and perPage
    $currentPageGroupIds = array_slice($groupIds->toArray(), ($currentPage - 1) * $perPage, $perPage);

    // Get the data for the current page's group_ids
    $currentPageData = collect();
    foreach ($currentPageGroupIds as $groupId) {
        $currentPageData = $currentPageData->merge($stockRequests[$groupId]);
    }

    return response([
        'message' => 'Successfully retrieved stock requests',
        'data' => $currentPageData,
        'current_page' => $currentPage,
        'last_page' => $totalPages,
    ], 200);
}







public function approve(Request $request)
{
    $validator = Validator::make($request->all(), [
        'approved_by' => 'required|numeric',
        'group_id' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response([
            'errors' => $validator->errors()
        ], 422);
    }

    $approvedBy = $request->input('approved_by');
    $groupId = $request->input('group_id');

    $stockRequests = StockRequest::where('group_id', $groupId)->get();

    DB::beginTransaction();

    try {
        foreach ($stockRequests as $request) {
         
            if ($approvedBy !== 0) {
                $inventoryEntry = InventoryEntry::find($request->item_id);

                if ($inventoryEntry->quantity_left < $request->quantity) {
                    return response([
                        'message' => 'Not enough quantity left for item ' . $request->item_id,
                    ], 422);
                }

                $inventoryEntry->quantity_left -= $request->quantity;
                $inventoryEntry->save();
            }

            $request->approved_by = $approvedBy;
            $request->save();
        }

        DB::commit();

        return response([
            'message' => 'Successfully approved stock requests',
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();

        return response([
            'message' => 'Failed to approve stock requests',
            'error' => $e->getMessage(),
        ], 500);
    }
}






}
