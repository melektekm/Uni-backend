<?php

namespace App\Http\Controllers;

use App\Models\DepartmentOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DepartmentOrderController extends Controller
{
    public function placeOrder(Request $request)
    {

        $data = $request->all();

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


         $filePath = $file->store('files','public');


    


            unset($data['file']);
            $data['file_path'] =   $filePath;
        }
        else{
            return response()->json(['error' => 'Failed to create department order. Please try again.'], 500);
        }

        $validator = Validator::make($data, [
            'department_id' => 'required|exists:departments,id',
            'file_path' => 'nullable|string',
            'lunch_price_per_person' => 'nullable|numeric',
            'refreshment_price_per_person' => 'nullable|numeric',
            'refreshment_per_day' => 'nullable|integer',
            'number_of_people' => 'required|integer|min:1',
            'number_of_days' => 'required|integer|min:1',
            'serving_date_start' => 'nullable|date',
            'serving_date_end' => 'nullable|date|after_or_equal:serving_date_start',
            'buyer_id' => 'required|exists:employees,id',
            'total_price' => 'required|numeric|min:0',
        ]);
        

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

       

        $data['lunch_price_per_person'] = max(0, $data['lunch_price_per_person']);
        $data['refreshment_per_day'] = max(0, $data['refreshment_per_day']);
        $data['refreshment_price_per_person'] = max(0, $data['refreshment_price_per_person']);


        $calculatedTotalPrice = ($data['number_of_people'] * $data['number_of_days']) *
            ($data['lunch_price_per_person'] +
                ($data['refreshment_price_per_person'] * $data['refreshment_per_day']));


        if ($calculatedTotalPrice != $data['total_price']) {
            return response()->json(['error' => 'Invalid total price. Please check your input.'], 422);
        }


        unset($data['total_price']);


        DB::beginTransaction();

        try {

            Transaction::create([
                'account_id' => null,
                'type' => 'departmentOrder',
                'amount' => $calculatedTotalPrice,
            ]);


            $departmentOrder = DepartmentOrder::create($data);


            DB::commit();


            return response()->json(['message' => 'Department order created successfully', 'data' => $departmentOrder], 201);
        } catch (\Exception $e) {

            DB::rollback();


            return response()->json(['error' => 'Failed to create department order. Please try again.'], 500);
        }
    }

    public function getAllOrders(Request $request)
    {
           $perPage = $request->input('per_page', 4);
$departmentOrders = DepartmentOrder::with('department')
    ->orderBy('created_at', 'desc') 
    ->paginate($perPage);
        
        
        foreach ($departmentOrders as $order) {
     
               
               
            $order->department_name = $order->department->name; 
            $order->buyer_name = $order->buyer->name; 
            unset($order->department);
            unset($order->buyer);
        }
        
        return response()->json($departmentOrders, 200);
    }

    public function getDepartmentOrderById($id, Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $departmentOrders = DepartmentOrder::where('department_id', $id)->paginate($perPage);

        if ($departmentOrders->isEmpty()) {
            return response()->json(['error' => 'Department orders not found for the given department ID'], 404);
        }

        return response()->json($departmentOrders, 200);
    }
}



