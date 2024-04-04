<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Employee;

class DepartmentController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'parent_id' => 'nullable|exists:departments,id',
         
        ]);

        $department = new Department();
        $department->name = $request->input('name');
        $department->parent_id = $request->input('parent_id');
       
        $department->save();

        return response([
            'message' => "በተሳካ ሁኔታ ገብቷል።",

        ], 200);
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:departments,id',
      
        ]);
    
        try {
            $department = Department::findOrFail($id);
            $department->name = $request->input('name');
            $department->parent_id = $request->input('parent_id');
            $department->save();
    
            return response([
                'message' => "በተሳካ ሁኔታ ገብቷል።",
            ], 200);
        } catch (\Exception $e) {
            return response([
                'message' => "አልተሳካም ደግመው ይሞክሩ",
            ], 422); 
        }
    }
    
    

    public function getById($id)
    {
        $department = Department::find($id);

        if (!$department) {
            return response()->json(['error' => 'ክፍል አልተገኘም።'], 404);
        }

        return response()->json(['department' => $department]);
    }
    public function getAllPagination()
    {
        $departments = Department::with('parent')->paginate(20);

        $departments->getCollection()->transform(function ($department) {
            $department->parent_id = $department->parent ? $department->parent->name : null;
            unset($department->parent);
            return $department;
        });

        return response()->json($departments);
    }

    public function getAll()
    {
        $departments = Department::all();

        return response()->json(['departments' => $departments]);
    }


    public function deleteDepartment($id)
    {
        try {
            $department = Department::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'ክፍል አልተገኘም'], 404);
        }

        $department->delete();

        return response()->json(['message' => 'ክፍል ተሰርዟል']);
    }


}

