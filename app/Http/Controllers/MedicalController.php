<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medical;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class MedicalController extends Controller
{
    private function getCurrentUser()
    {
        foreach (['admin', 'seller', 'parent'] as $guard) {
            if (auth($guard)->check()) {
                Auth::shouldUse($guard);
                return auth($guard)->user();
            }
        }

        return null;
    }

    private function getCurrentGuard()
    {
        foreach (['admin', 'seller', 'parent'] as $guard) {
            if (auth($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    public function index()
    {
        if (!$this->getCurrentUser()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $medical = Medical::with('student')->paginate(10);

        if ($medical->isEmpty()) {
            return response()->json(['message' => 'No medical Found'], 404);
        }

        $data = $medical->map(function ($medical) {
            return [
                'medical_name' => $medical->medical_name ?? 'غير محدد',
                'medical_file' => $medical->medical_file ?? 'غير متوفر',
                'student_name' => $medical->student->name ?? 'غير معروف',
            ];
        });

        return response()->json(['data' => $data], 200);
    }

    public function store(Request $request)
    {
        $user = $this->getCurrentUser();
        $guard = $this->getCurrentGuard();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'student_code' => 'required|integer',
            'medical_name' => 'required|string',
            'medical_file' => 'nullable|string',
        ]);

        $studentQuery = Student::where('student_code', $request->student_code);

        // إذا المستخدم parent نتأكد إن الطالب يخصه
        if ($guard === 'parent') {
            $studentQuery->where('parent_id', $user->id);
        }

        $student = $studentQuery->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found or not yours'], 404);
        }

        $medical = new Medical();
        $medical->student_id = $student->id;
        $medical->medical_name = $request->medical_name;
        $medical->medical_file = $request->medical_file;
        $medical->save();

        return response()->json(['message' => 'Medical record added successfully'], 200);
    }

    public function show($id)
    {
        if (!$this->getCurrentUser()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $medical = Medical::with('student')->find($id);

        if (!$medical) {
            return response()->json(['message' => 'Medical Not Found'], 404);
        }

        return response()->json([
            'medical_name' => $medical->medical_name ?? 'غير متوفر',
            'medical_file' => $medical->medical_file ?? 'غير متوفر',
            'student_name' => $medical->student->name ?? 'غير معروف',
            'student_code' => $medical->student->student_code ?? 'غير معروف',
            'created_at' => $medical->created_at,
            'updated_at' => $medical->updated_at,
        ], 200);
    }

    public function show_StuCode($StuCode)
    {
        if (!$this->getCurrentUser()) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $student = Student::where('student_code', $StuCode)->first();
        if (!$student) {
            return response()->json(['message' => 'Student Not Found'], 404);
        }

        $medical = Medical::where('student_id', $student->id)->first();
        if (!$medical) {
            return response()->json(['message' => 'Medical Not Found'], 404);
        }

        return response()->json([
            'medical_name' => $medical->medical_name ?? 'غير متوفر',
            'medical_file' => $medical->medical_file ?? 'غير متوفر',
            'student_name' => $student->name,
            'created_at' => $medical->created_at,
            'updated_at' => $medical->updated_at,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $guard = $this->getCurrentGuard();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'student_code' => 'required|integer',
            'medical_name' => 'nullable|string',
            'medical_file' => 'nullable|string',
        ]);

        $studentQuery = Student::where('student_code', $request->student_code);

        if ($guard === 'parent') {
            $studentQuery->where('parent_id', $user->id);
        }

        $student = $studentQuery->first();
        if (!$student) {
            return response()->json(['error' => 'Student not found or not yours'], 404);
        }

        $medical = Medical::find($id);
        if (!$medical) {
            return response()->json(['error' => 'Medical not found'], 404);
        }

        $medical->student_id = $student->id;
        if ($request->has('medical_name')) {
            $medical->medical_name = $request->medical_name;
        }
        if ($request->has('medical_file')) {
            $medical->medical_file = $request->medical_file;
        }
        $medical->save();

        return response()->json(['message' => 'Medical updated successfully'], 200);
    }

    public function destroy($id)
    {
        $user = $this->getCurrentUser();
        $guard = $this->getCurrentGuard();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $medical = Medical::with('student')->find($id);

        if (!$medical) {
            return response()->json(['message' => 'Medical Not Found'], 404);
        }

        if ($guard === 'parent' && $medical->student->parent_id !== $user->id) {
            return response()->json(['error' => 'You are not authorized to delete this record'], 403);
        }

        $studentName = $medical->student->name ?? 'غير معروف';
        $medical->delete();

        return response()->json([
            'message' => 'Deleted Successfully',
            'student_name' => $studentName,
        ], 200);
    }
}
