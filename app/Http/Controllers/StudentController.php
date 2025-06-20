<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\student;
use App\Models\parents;

class StudentController extends Controller
{
    // ✅ دالة جديدة للتحقق من صلاحية المستخدم (admin أو parent فقط)
    private function checkAdminOrParent()
    {
        return auth('parent')->check() || auth('admin')->check();
    }

    public function index()
    {
        if (!$this->checkAdminOrParent()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $students = student::with('parent')->paginate(10);

        $students->getCollection()->transform(function ($student) {
            return [
                'id' => $student->id,
                'student_code' => $student->student_code,
                'name' => $student->name,
                'age' => $student->age,
                'budget' => $student->budget,
                'QRCode' => $student->QRCode,
                'parent_name' => $student->parent->name ?? 'غير معروف',
            ];
        });

        return response()->json($students, 200);
    }

    public function store(Request $request)
    {
        if (!$this->checkAdminOrParent()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'age' => 'required|integer',
                'student_code' => 'required|integer',
                'budget' => 'required',
                'QRCode' => 'required|string',
            ]);

            $parent = auth('parent')->user();
            $admin  = auth('admin')->user();

            if (!$parent && !$admin) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $student = new Student();
            $student->name = $request->name;
            $student->age = $request->age;
            $student->student_code = $request->student_code;
            $student->budget = $request->budget;
            $student->QRCode = $request->QRCode;

            if ($parent) {
                $student->parent_id = $parent->id;
            }

            $student->save();

            return response()->json('Student Added', 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        if (!$this->checkAdminOrParent()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = Student::with('parent')->find($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json([
            'id' => $student->id,
            'student_code' => $student->student_code,
            'name' => $student->name,
            'age' => $student->age,
            'budget' => $student->budget,
            'QRCode' => $student->QRCode,
            'parent_name' => $student->parent->name ?? 'غير معروف',
        ], 200);
    }

    public function show_StuCode($StuCode)
    {
        if (!$this->checkAdminOrParent()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = Student::with('parent')->where('student_code', $StuCode)->first();

        if ($student) {
            return response()->json([
                'id' => $student->id,
                'student_code' => $student->student_code,
                'name' => $student->name,
                'age' => $student->age,
                'budget' => $student->budget,
                'QRCode' => $student->QRCode,
                'parent_name' => $student->parent->name ?? 'غير معروف',
            ], 200);
        } else {
            return response()->json(['message' => 'Student Not Found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        if (!$this->checkAdminOrParent()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'age' => 'required|integer',
                'student_code' => 'required|integer',
                'budget' => 'required',
                'QRCode' => 'required|string',
            ]);

            $parent = auth('parent')->user();
            $admin  = auth('admin')->user();

            $student = student::where('id', $id);

            // لو المستخدم Parent فقط، تأكد إنه بيعدل بس ولاده
            if ($parent) {
                $student->where('parent_id', $parent->id);
            }

            $student = $student->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or unauthorized'], 404);
            }

            $student->update([
                'name' => $request->name,
                'age' => $request->age,
                'student_code' => $request->student_code,
                'budget' => $request->budget,
                'QRCode' => $request->QRCode,
            ]);

            return response()->json(['message' => 'Student updated successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!$this->checkAdminOrParent()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $student = student::find($id);
        if ($student) {
            $student->delete();
            return response()->json('Deleted Successfully', 200);
        } else {
            return response()->json('Student Not Found');
        }
    }
}
