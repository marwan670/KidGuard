<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medical;
use App\Models\Product;
use App\Models\student;
use App\Models\product_restriction;
use App\Models\product_selected;

class ProductRestrictonsController extends Controller
{
    private function checkAuth()
    {
        foreach (['admin', 'seller', 'parent'] as $guard) {
            if (auth($guard)->check()) {
                return true;
            }
        }
        return false;
    }

   public function index()
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product_restrictions = product_restriction::with(['medical.student', 'product'])->paginate(10);

        $data = $product_restrictions->map(function ($item) {
            $studentId = $item->medical->student_id ?? null;

            $selection = \App\Models\Product_selected::where('product_id', $item->product_id)
                ->where('student_id', $studentId)
                ->first();

            return [
                'product_name'   => $item->product->name ?? 'غير معروف',
                'medical_name'   => $item->medical->medical_name ?? 'غير معروف',
                'status'         => $selection->status ?? 'غير معروف',
            ];
        });

        return response()->json(['data' => $data], 200);
    }


    public function show($id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product_restriction = product_restriction::with(['medical.student', 'product'])->find($id);

        if (!$product_restriction) {
            return response()->json(['message' => 'Product restriction not found'], 404);
        }

        $studentId = $product_restriction->medical->student_id ?? null;

        $selection = \App\Models\Product_selected::where('product_id', $product_restriction->product_id)
            ->where('student_id', $studentId)
            ->first();

        return response()->json([
            'product_name'  => $product_restriction->product->name ?? 'غير معروف',
            'medical_name'  => $product_restriction->medical->medical_name ?? 'غير معروف',
            'status'        => $selection->status ?? 'غير معروف',
        ], 200);
    }


    public function show_StuCode($StuCode)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $student = Student::where('student_code', $StuCode)->first();

        if (!$student) {
            return response()->json(['message' => 'Student Not Found'], 404);
        }

        $selections = Product_selected::where('student_id', $student->id)->get();

        if ($selections->isEmpty()) {
            return response()->json(['message' => 'No selections found'], 404);
        }

        $data = [];

        foreach ($selections as $selection) {
            $product = Product::find($selection->product_id);
            $productName = $product ? $product->name : 'غير معروف';

            $medicalNames = [];

            if ($selection->status === 'rejected') {
               
                $studentMedicalIds = Medical::where('student_id', $student->id)->pluck('id')->toArray();

                $restrictions = Product_Restriction::where('product_id', $selection->product_id)
                    ->whereIn('medical_id', $studentMedicalIds)
                    ->get();

                foreach ($restrictions as $restriction) {
                    $medical = Medical::find($restriction->medical_id);
                    if ($medical) {
                        $medicalNames[] = $medical->medical_name;
                    }
                }
            }

            $data[] = [
                'product_name' => $productName,
                'medical_names' => $medicalNames, 
                'status' => $selection->status,
            ];
        }

        return response()->json([
            'selections' => $data,
        ], 200);
    }



    public function update(Request $request, $id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $product_restriction = product_restriction::with('medical')->find($id);

        if (!$product_restriction) {
            return response()->json(['message' => 'Product restriction not found'], 404);
        }

        $studentId = $product_restriction->medical->student_id ?? null;
        $productId = $product_restriction->product_id;

        if (!$studentId) {
            return response()->json(['message' => 'Student ID not found in medical record'], 404);
        }

        $product_selected = \App\Models\Product_selected::where('student_id', $studentId)
            ->where('product_id', $productId)
            ->first();

        if (!$product_selected) {
            return response()->json(['message' => 'Product selection not found for this student'], 404);
        }

        $product_selected->status = $validated['status'];
        $product_selected->save();

        return response()->json(['message' => 'Status updated successfully'], 200);
    }


    public function destroy($id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product_restriction = product_restriction::find($id);

        if (!$product_restriction) {
            return response()->json(['message' => 'Product Selected Not Found'], 404);
        }

        $product_restriction->delete();

        return response()->json(['message' => 'Product Selected Deleted Successfully'], 200);
    }
}
