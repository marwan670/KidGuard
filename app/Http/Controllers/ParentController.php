<?php

namespace App\Http\Controllers;

use App\Http\Requests\Registerparent;
use App\Models\parents;
use Illuminate\Http\Request;
use App\Events\verfiyEmailAddressCode;

class ParentController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    // public function resendActiveCode(Request $request)
    // {
    //     $data = $request->validate(['code_type' => 'required|in:email,phone']);
    //     if ($request->code_type == 'phone') {
    //         //
    //     } else if ($request->code_type == 'email') {
    //         event(new verfiyEmailAddressCode(parents::find(auth()->user()->id)));
    //     }
    // }

    /**
     * Get a JWT via given credentials.
     *
     */

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = auth('parent')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['token' => $token]);
    }


    public function register(Registerparent $request)
    {
        $validatedData = $request->validated();

        $validatedData['password'] = bcrypt($validatedData['password']);

        $parent = parents::create($validatedData);

        $credentials = [
            'email' => $parent->email,
            'password' => $request->password,
        ];

        if (! $token = auth('parent')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }




    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('parent')->user());
    }



    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('parent')->logout();
        return response()->json(['message' => 'Logged out successfully']);
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 99999
        ]);
    }
}
