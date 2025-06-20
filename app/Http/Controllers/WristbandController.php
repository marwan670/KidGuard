<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\wristband;
use App\Models\student;

class WristbandController extends Controller
{
    public function index()
    {
        $wristbands = Wristband::with('student')->paginate(10);

        if ($wristbands->isEmpty()) {
            return response()->json(['message' => 'No Wristbands Found'], 404);
        }

        $data = $wristbands->map(function ($wristband) {
            return [
                'student_name' => $wristband->student->name ?? 'غير معروف',
                'student_code' => $wristband->student->student_code ?? 'غير معروف',
                'created_at' => $wristband->created_at,
            ];
        });

        return response()->json([
            'data' => $data
        ], 200);
    }



    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_code' => 'required|integer',
            ]);

            $parent = auth('parent')->user();

            if (!$parent) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $student = student::where('student_code', $request->student_code)
                            ->where('parent_id', $parent->id)
                            ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or not yours'], 404);
            }

            $wristband = new Wristband();
            $wristband->student_id = $student->id;
            $wristband->save();

            return response()->json('Wristband Added', 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $wristband = Wristband::with('student')->find($id);

        if (!$wristband) {
            return response()->json(['message' => 'Wristband Not Found'], 404);
        }

        return response()->json([
            'student_name' => $wristband->student->name ?? 'غير معروف',
            'student_code' => $wristband->student->student_code ?? 'غير معروف',
            'created_at' => $wristband->created_at,
            'updated_at' => $wristband->updated_at,
        ], 200);
    }


    public function show_StuCode($StuCode)
    {
        
        $student = student::where('student_code', $StuCode)->first();

        if (!$student) {
            return response()->json(['message' => 'Student Not Found'], 404);
        }

        $wristband = Wristband::where('student_id', $student->id)->first();

        if (!$wristband) {
            return response()->json(['message' => 'Wristband Not Found'], 404);
        }

        return response()->json([
            'student_code' => $student->student_code,
            'student_name' => $student->name,
            'created_at' => $wristband->created_at,
        ], 200);
    }



    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'student_code' => 'required|integer',
            ]);

            $parent = auth('parent')->user();

            if (!$parent) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $student = student::where('student_code', $request->student_code)
                            ->where('parent_id', $parent->id)
                            ->first();

            if (!$student) {
                return response()->json(['error' => 'Student not found or not yours'], 404);
            }

            $wristband = Wristband::find($id);

            if (!$wristband) {
                return response()->json(['error' => 'Wristband not found'], 404);
            }

            $wristband->student_id = $student->id;
            $wristband->save();

            return response()->json(['message' => 'Wristband updated successfully'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $wristband = Wristband::with('student')->find($id);

        if (!$wristband) {
            return response()->json(['message' => 'Wristband Not Found'], 404);
        }

        $studentName = $wristband->student->name ?? 'غير معروف';
        $wristband->delete();

        return response()->json([
            'message' => 'Deleted Successfully',
            'student_name' => $studentName,
        ], 200);
    }

}
