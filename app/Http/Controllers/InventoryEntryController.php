<?php

namespace App\Http\Controllers;

use App\Models\InventoryEntry;
use App\Models\InventoryRequest;
use Illuminate\Http\Request;
use App\Models\InventoryRequestEntry;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class InventoryEntryController extends Controller
{


public function createInventoryEntryList(Request $request)
{

    $fileUrl = null;
    if ($request->hasFile('file')) {
        $file = $request->file('file');


        if ($file->getSize() > 3 * 1024 * 1024) {
            return response()->json(['error' => 'File size exceeds the allowed limit of 3 MB.'], 422);
        }


        $allowedFileTypes = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = $file->getClientOriginalExtension();

        if (!in_array($extension, $allowedFileTypes)) {
            return response()->json(['error' => 'Invalid file format. Allowed formats: ' . implode(', ', $allowedFileTypes)], 422);
        }


        try {
           
            
            
               $filePath = $file->store('files','public');


            $fileUrl =   $filePath ;


   
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to store the file.'], 422);
        }

    }else{
        return response([
            'error' => 'An error occurred while processing the request.',
            'message' => 'An error occurred while processing the request.',
        ], 500);

    }
    

    $validator = Validator::make($request->all(), [
        'items.*.name' => 'required',
        'items.*.quantity' => 'required|numeric',
        'items.*.measured_in' => 'required',
        'items.*.price_word' => 'nullable',
        'items.*.price_per_item' => 'required|numeric',
        'total_price_entry' => 'required',
        'entry_approved_by' => 'required',
        'returned_amount' => 'required',
        'file'=> 'required',
        'id'=>'nullable'
    ]);

    if ($validator->fails()) {

        return response([
            'errors' => $validator->errors()
        ], 422);
    }
    if ($fileUrl === null || !is_string($fileUrl) || empty($fileUrl)) {
        return response()->json(['error' => 'Unprocessable Entity. File is missing or invalid.'], 433);
    }
    $id = $request->id;

    DB::beginTransaction();

    try {
        $startId = null;
        $endId = null;

        $items = $request->input('items');

        foreach ($items as $item) {
            // Assuming $item is an associative array, not an object
            $item['quantity_left'] = $item['quantity'];
            $inventoryReq = InventoryEntry::create($item);

            if ($startId === null) {
                $startId = $inventoryReq->id;
            }

            $endId = $inventoryReq->id;
        }

        if ($id !== null) {
            $entry = InventoryRequestEntry::find($id);
            if (!$entry) {
                return response([
                    'message' => 'Entry not found with the provided ID.',
                ], 404);
            }

            $entry->submitted_items_start_id = $startId;
            $entry->submitted_items_end_id = $endId;
            $entry->file_path = $fileUrl;
            $entry->total_price_entry = $request->input('total_price_entry');
            $entry->returned_amount = $request->input('returned_amount');
            $entry->entry_approved_by = $request->input('entry_approved_by');
            $entry->save();
        } else {
            $entry = new InventoryRequestEntry;
            $entry->submitted_items_start_id = $startId;
            $entry->submitted_items_end_id = $endId;
            $entry->file_path = $fileUrl;
            $entry->total_price_entry = $request->input('total_price_entry');
            $entry->returned_amount = $request->input('returned_amount');
            $entry->entry_approved_by = $request->input('entry_approved_by');
            $entry->save();
        }

        DB::commit();

        return response([
            'message' => "Successfully added or updated entries",
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


public function getAllInventoryData(Request $request)
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

    $query->select([
        'id',
        'total_price_entry',
        'entry_approved_by',
        'returned_amount',
        'submitted_items_start_id',
        'submitted_items_end_id',
        'purchase_request_start_id',
        'file_path',
        'created_at',
    ]);

   
    
    $inventoryData = $query->where('submitted_items_start_id', '>', 0)
    ->orderBy('created_at', 'desc')
    ->with(['entryApprovedBy:id,name'])
    ->paginate($perPage);

  
    foreach ($inventoryData as $entry) {

        $startEntry = InventoryEntry::find($entry->submitted_items_start_id);

        $entry->formatted_created_at = $startEntry ?  $startEntry->created_at->format('Y-m-d h:i:s A') : null;

        $relatedEntries = InventoryEntry::whereBetween('id', [$entry->submitted_items_start_id, $entry->submitted_items_end_id])->get(); 

        $entry->related_requests =$relatedEntries->map(function ( $relatedEntry) {
        unset($relatedEntry->created_at,$relatedEntry->updated_at,$relatedEntry->quantity_left,);
        return  $relatedEntry;
         });
  
    unset($entry->submitted_items_end_id,$entry->created_at,);
    
     

    }
    return response([
        'message' => 'Successfully retrieved inventory data within the specified time range',
        'data' => $inventoryData,
    ], 200);
}


public function getInventoryDataById($entryId)
{


    $query = InventoryRequestEntry::query();

    $query->select([
        'id',
        'total_price_entry',
        'entry_approved_by',
        'returned_amount',
        'submitted_items_start_id',
        'submitted_items_end_id',
        'file_path',
        'created_at',
    ]);
    

    $inventoryRequest = $query->with(['entryApprovedBy:id,name'])
    ->where('id', $entryId)
    ->first();
    if (!$inventoryRequest) {
        return response([
            'message' => 'Inventory request not found with the provided ID.',
        ], 404);
    }
    


    $startEntry = InventoryEntry::find( $inventoryRequest->submitted_items_start_id);

    $inventoryRequest->formatted_created_at = $startEntry ?  $startEntry->created_at->format('Y-m-d h:i:s A') : null;
   
    $relatedRequests = InventoryEntry::whereBetween('id', [$inventoryRequest->submitted_items_start_id, $inventoryRequest->submitted_items_end_id])->get();
   
    $inventoryRequest->related_requests = $relatedRequests->map(function ($relatedRequest) {
        unset($relatedRequest->created_at, $relatedRequest->updated_at, $relatedRequest->quantity_left);
        return $relatedRequest;
    });
      

    $responseData = [
        'message' => 'Successfully retrieved inventory entry with the provided ID',
        'data' => [$inventoryRequest],
    ];

    return response($responseData, 200);
  
}

public function getAllInventory(Request $request)
{
    $perPage = $request->input('per_page', 10);

    $query = InventoryEntry::query();

    $query->select([
        'id',
        'name',
        'quantity_left',
        'measured_in',
    ]);

    $inventoryData = $query->where('quantity_left', '>', 0)
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    $data = [];
    foreach ($inventoryData as $entry) {
        $data[] = [
            'id' => $entry->id,
            'name' => $entry->name,
            'quantity_left' => $entry->quantity_left,
            'measured_in' => $entry->measured_in,
        ];
    }

    return response([
        'message' => 'Successfully retrieved inventory data within the specified time range',
        'data' => $data,
    ], 200);
}

}








