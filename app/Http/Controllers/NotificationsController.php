<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\product_selected;
use App\Models\student;
use App\Models\seller;
use App\Models\product;
use App\Models\medical;
use App\Models\notification;


class NotificationsController extends Controller
{
    private function checkAuth()
    {
        foreach (['admin', 'parent'] as $guard) {
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

        $notifications = \App\Models\Notification::with(['parent', 'admin'])->paginate(10);

        $data = $notifications->map(function ($item) {
            return [
                'parent_name' => $item->parent->name ?? 'غير معروف',
                'admin_name'  => $item->admin->name ?? 'غير معروف',
                'message'     => $item->message,
            ];
        });

        return response()->json(['data' => $data], 200);
    }




    public function show($id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = \App\Models\Notification::with(['parent', 'admin'])->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json([
            'parent_name' => $notification->parent->name ?? 'غير معروف',
            'admin_name'  => $notification->admin->name ?? 'غير معروف',
            'message'     => $notification->message,
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

        $notifications = \App\Models\Notification::where('parent_id', $student->parent_id)->get();

        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'No notifications found'], 404);
        }

        $data = $notifications->map(function ($notification) {
            return [
                'parent_id' => $notification->parent_id,
                'admin_id'  => $notification->admins_id,
                'message'   => $notification->message,
            ];
        });

        return response()->json([
            'notifications' => $data,
        ], 200);
    }





//   public function update(Request $request, $id)
//     {
//         if (!$this->checkAuth()) {
//             return response()->json(['message' => 'Unauthorized'], 401);
//         }

//         $validated = $request->validate([
//             'status' => 'required|in:accepted,rejected',
//         ]);

//         $notification = \App\Models\Notification::find($id);

//         if (!$notification) {
//             return response()->json(['message' => 'Notification not found'], 404);
//         }

//         // نجيب parent (ولي الأمر)
//         $parent = \App\Models\Parents::find($notification->parent_id);
//         $admin  = \App\Models\Admin::find($notification->admin_id);

//         if (!$parent || !$admin) {
//             return response()->json(['message' => 'Missing parent or admin info'], 404);
//         }

//         // نجيب الطالب من خلال علاقة الأبناء
//         $student = \App\Models\Student::where('parent_id', $parent->id)->first();

//         if (!$student) {
//             return response()->json(['message' => 'Student not found'], 404);
//         }

//         // نجيب كل product_selected اللي تخص الطالب
//         $product_selected = \App\Models\Product_selected::where('student_id', $student->id)
//             ->whereHas('product', function ($q) use ($notification) {
//                 $q->where('name', 'like', '%' . $notification->message . '%');
//             })
//             ->first();

//         if (!$product_selected) {
//             return response()->json(['message' => 'Product selection not found for this student'], 404);
//         }

//         $product_selected->status = $validated['status'];
//         $product_selected->save();

//         return response()->json(['message' => 'Status updated successfully'], 200);
//     }




    public function destroy($id)
    {
        if (!$this->checkAuth()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Product Selected Not Found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Product Selected Deleted Successfully'], 200);
    }
}
