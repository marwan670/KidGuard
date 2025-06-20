<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\product;

class ProductController extends Controller
{
    // تحقق من أن المستخدم إما seller أو admin
    private function checkAuthForAdminOrSeller()
    {
        foreach (['admin', 'seller'] as $guard) {
            if (auth($guard)->check()) {
                return true;
            }
        }
        return false;
    }

    public function index()
    {
        if (!$this->checkAuthForAdminOrSeller()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = product::paginate(10);

        if ($product) {
            return response()->json($product, 200); 
        } else {
            return response()->json('No product');
        }
    }

    public function store(Request $request)
    {
        if (!$this->checkAuthForAdminOrSeller()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'price' => 'required',
                'product_detials' => 'required|string',
            ]);

            $product = new product();
            $product->name = $request->name;
            $product->price = $request->price;
            $product->product_detials = $request->product_detials;
            $product->save();

            return response()->json('Product Added', 200); 
        } catch (\Exception $e) {
            return response()->json($e, 500); 
        }
    }

    public function show($id)
    {
        if (!$this->checkAuthForAdminOrSeller()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = product::find($id);

        if ($product) {
            return response()->json($product, 200); 
        } else {
            return response()->json('Product Not Found');
        }
    }

    public function show_name($name)
    {
        if (!$this->checkAuthForAdminOrSeller()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::where('name', 'like', "%$name%")->first();

        if ($product) {
            return response()->json($product, 200);
        }

        return response()->json(['message' => 'Product Not Found'], 404);
    }

    public function update(Request $request, $id)
    {
        if (!$this->checkAuthForAdminOrSeller()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'price' => 'required',
                'product_detials' => 'required|string',
            ]);

            $product = product::where('id', $id)->update([
                'name' => $request->name,
                'price' => $request->price,
                'product_detials' => $request->product_detials,
            ]);

            return response()->json('Product Updated', 200); 
        } catch (\Exception $e) {
            return response()->json($e, 500); 
        }
    }

    public function destroy($id)
    {
        if (!$this->checkAuthForAdminOrSeller()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = product::find($id);

        if ($product) {
            $product->delete();
            return response()->json('Deleted Successfully', 200); 
        } else {
            return response()->json('Product Not Found');
        }
    }
}
