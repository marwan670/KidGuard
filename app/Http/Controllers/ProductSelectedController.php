<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\product_selected;
use App\Models\student;
use App\Models\seller;
use App\Models\product;
use App\Models\medical;
use App\Models\product_restriction;

class ProductSelectedController extends Controller
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

        $products_selected = Product_selected::with(['student', 'product', 'seller'])->paginate(10);

        $data = $products_selected->map(function ($item) {
            return [
                'student_name' => $item->student->name ?? 'غير معروف',
                'product_name' => $item->product->name ?? 'غير معروف',
                'seller_name'  => $item->seller->name ?? 'غير معروف',
                'status'       => $item->status,
            ];
        });

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
{
    if (!$this->checkAuth()) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    try {
        $validated = $request->validate([
            'name' => 'required|string',
            'student_code' => 'required|string',
        ]);

        $seller = auth('seller')->user();
        if (!$seller) {
            return response()->json(['message' => 'Seller not authenticated'], 401);
        }

        $student = Student::where('student_code', $request->student_code)->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $product = Product::where('name', $request->name)->first();
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $status = 'accepted';
        $conflictingMedical = null;

        // ✅ استخدام اسم العمود الصحيح: product_detials
        $product_components = [];
        if (!empty($product->product_detials)) {
            $product_components = array_map('trim', explode(',', strtolower($product->product_detials)));
        }

        $student_medicals = Medical::where('student_id', $student->id)->get();

        foreach ($student_medicals as $medical) {
            $medicalName = strtolower(trim($medical->medical_name ?? ''));
            $medicalFile = strtolower(trim($medical->medical_file ?? ''));

            foreach ($product_components as $component) {
                if ($component !== '' && (
                    $medicalName === $component ||
                    $medicalFile === $component ||
                    strpos($medicalName, $component) !== false ||
                    strpos($medicalFile, $component) !== false
                )) {
                    $status = 'rejected';
                    $conflictingMedical = $medical;
                    break 2;
                }
            }
        }

        if ($status === 'accepted') {
            if ($student->budget < $product->price) {
                return response()->json(['message' => 'Insufficient student budget'], 400);
            }

            $student->budget -= $product->price;
            $student->save();
        }

        $product_selected = new Product_selected();
        $product_selected->student_id = $student->id;
        $product_selected->product_id = $product->id;
        $product_selected->seller_id  = $seller->id;
        $product_selected->status     = $status;
        $product_selected->save();

        if ($status === 'rejected' && $conflictingMedical) {
            \App\Models\product_restriction::create([
                'product_id' => $product->id,
                'medical_id' => $conflictingMedical->id,
            ]);

            $parent = $student->parent;
            $admin = \App\Models\Admin::first();

            $message = 'The student ' . $student->name . ' attempted to purchase the product ' . $product->name . ', but the request was rejected due to their medical condition.';

            \App\Models\Notification::create([
                'parent_id' => $parent->id,
                'admin_id' => $admin->id,
                'message' => $message,
                'sent_at' => now(),
            ]);
        }

        $messageResponse = ($status === 'rejected')
            ? 'The request was rejected due to a medical conflict.'
            : 'Product selection saved successfully.';

        return response()->json([
            'message' => $messageResponse,
            'status' => $status
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }
}




    public function show($id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product_selected = Product_selected::with(['student', 'product', 'seller'])->find($id);

        if (!$product_selected) {
            return response()->json(['message' => 'Product Selected Not Found'], 404);
        }

        return response()->json([
            'id' => $product_selected->id,
            'student_name' => $product_selected->student->name ?? 'غير معروف',
            'product_name' => $product_selected->product->name ?? 'غير معروف',
            'seller_name'  => $product_selected->seller->name ?? 'غير معروف',
            'status'       => $product_selected->status,
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

        $products = Product_selected::where('student_id', $student->id)
            ->with('product:id,name')
            ->get();

        if ($products->isEmpty()) {
            return response()->json(['message' => 'No products found for this student'], 404);
        }

        return response()->json([
            'student_name' => $student->name,
            'products'     => $products->pluck('product.name'),
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $request->validate([
                'status' => 'required|in:accepted,rejected',
            ]);

            $product_selected = Product_selected::find($id);

            if (!$product_selected) {
                return response()->json(['message' => 'Product Selected Not Found'], 404);
            }

            $product_selected->status = $request->status;
            $product_selected->save();

            return response()->json(['message' => 'Product Selected Updated'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product_selected = Product_selected::find($id);

        if (!$product_selected) {
            return response()->json([
                'message' => 'Product Selected Not Found'
            ], 404);
        }

        $product_selected->delete();

        return response()->json([
            'message' => 'Product Selected Deleted Successfully'
        ], 200);
    }
}


