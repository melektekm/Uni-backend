<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\InventoryRequest;
use App\Models\InventoryRequestEntry;
use Illuminate\Support\Facades\DB;


class

  InventoryRequestController extends Controller
{



    public function createInventoryList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items.*.name' => 'required',
            'items.*.quantity' => 'required|numeric',
            'items.*.measured_in' => 'required',
            'items.*.price_word' => 'nullable',
            'items.*.price_per_item' => 'required|numeric',
            'total_price_request' => 'required',
            'recommendations' => 'nullable',
            'requested_by' => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $startId = null;
            $endId = null;

            $items = $request->input('items');
            foreach ($items as $item) {
                $inventoryReq = InventoryRequest::create($item);

                if ($startId === null) {
                    $startId = $inventoryReq->id;
                }

                $endId = $inventoryReq->id;
            }

            $inventoryRequestEntry = new InventoryRequestEntry();
            $inventoryRequestEntry->purchase_request_start_id = $startId;
            $inventoryRequestEntry->purchase_request_end_id = $endId;
            $inventoryRequestEntry->total_price_request = $request->input('total_price_request');
            $inventoryRequestEntry->recommendations = $request->input('recommendations');
            $inventoryRequestEntry->requested_by = $request->input('requested_by');
            $inventoryRequestEntry->save();

            DB::commit();

            return response([
                'message' => "Successfully added requests and created an entry",
                'created_ids' => [$startId, $endId],
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                'error' => 'An error occurred while processing the request.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function InventoryRequestApproval(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'numeric|required',
             'user_id'=> 'numeric|required',
        ]);
        if ($validator->fails()) {
            return response([
                'errors' => $validator->errors()
            ], 422);
        }
        $id=$request->id;
        $entry = InventoryRequestEntry::find($id);
        if (!$entry) {
            return response([
                'message' => 'Entry not found with the provided ID.',
            ], 404);
        }
      
       
        $entry->request_status= $request->status;
        
        $entry->request_approved_by = $request->user_id;
        $entry->save();
        return response([
            'message' => "Status updated Successfully ",
            'data'=> $id
        ], 200);


    }



    // public function getAllInventoryRequests(Request $request)
    // {
    //     $perPage = $request->input('per_page', 10);
    //     $inventoryRequests = inventoryRequest::all();

    //     return response([
    //         'message' => 'Successfully retrieved all inventory requests',
    //         'requests' => $inventoryRequests,
    //     ], 200);
    // }
//     public function getAllInventoryRequests(Request $request)
// {
//     $perPage = $request->input('per_page', 10);
//     $inventoryRequests = InventoryRequest::orderBy('date', 'desc')->get();

    //     return response([
//         'message' => 'Successfully retrieved all inventory requests',
//         'requests' => $inventoryRequests,
//     ], 200);
// }



public function getAllInventoryRequests(Request $request)
{
    $perPage = $request->input('per_page', 1);

    $query = InventoryRequestEntry::query();

    if ($request->input('today')) {
        $query->whereDate('created_at', Carbon::today());
    }

    if ($request->input('this_week')) {
        $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    }

    if ($request->input('this_month')) {
        $query->whereMonth('created_at', Carbon::now()->month);
    }
    $query->whereNotNull('purchase_request_start_id');

    $query->select([
        'id',
        'total_price_request',
        'recommendations',
        'requested_by',
        'created_at',
        'purchase_request_start_id',
        'purchase_request_end_id',
        'request_approved_by',
        'request_status',
        'submitted_items_start_id',

    ]);

    $inventoryRequests = $query->orderBy('created_at', 'desc')->with(['requestedBy:id,name'])->paginate($perPage);



    foreach ($inventoryRequests as $entry) {

        
        
        $entry->formatted_created_at = $entry->created_at->format('Y-m-d h:i:s A');
   
        $relatedRequests = InventoryRequest::whereBetween('id', [$entry->purchase_request_start_id, $entry->purchase_request_end_id])->get();
      
        $entry->related_requests =  $relatedRequests->map(function ($relatedRequest) {
        unset($relatedRequest->created_at,$relatedRequest->updated_at,);
        return $relatedRequest;
    });
    unset($entry->purchase_request_start_id,$entry->purchase_request_end_id,$entry->created_at,);


    }

    return response([
        'message' => 'Successfully retrieved inventory requests within the specified time range',
        'requests' => $inventoryRequests,
    ], 200);
}
public function getRequestById($reqId)
{
    $query = InventoryRequestEntry::query();

    $query->select([
        'id',
        'total_price_request',
        'recommendations',
        'requested_by',
        'created_at',
        'purchase_request_start_id',
        'purchase_request_end_id',
        'request_approved_by',
        'request_status',
        'submitted_items_start_id',
    ]);

    $inventoryRequest = $query->with(['requestedBy:id,name'])
                            ->where('id', $reqId)
                            ->first();

    if (!$inventoryRequest) {
        return response([
            'message' => 'Inventory request not found with the provided ID.',
        ], 404);
    }

    $inventoryRequest->formatted_created_at = $inventoryRequest->created_at->format('Y-m-d h:i:s A');

    $relatedRequests = InventoryRequest::whereBetween('id', [$inventoryRequest->purchase_request_start_id, $inventoryRequest->purchase_request_end_id])->get();

    $inventoryRequest->related_requests = $relatedRequests->map(function ($relatedRequest) {
        unset($relatedRequest->created_at, $relatedRequest->updated_at);
        return $relatedRequest;
    });

    unset($inventoryRequest->purchase_request_start_id, $inventoryRequest->purchase_request_end_id, $inventoryRequest->created_at);


    $responseData = [
        'message' => 'Successfully retrieved inventory request with the provided ID',
        'data' => [$inventoryRequest],
    ];

    return response($responseData, 200);
 
}



}
