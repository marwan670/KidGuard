<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterSeller;
use App\Models\seller;
use Illuminate\Http\Request;
use App\Events\verfiyEmailAddressCode;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;


class SellerController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        auth()->setDefaultDriver('seller');
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (!$token = auth('seller')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['token' => $token]);
    }


   public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:sellers,email',
            'password' => 'required|string|min:6',
            'phone'    => 'required|unique:sellers,phone',
            'age'      => 'required|integer|min:7',
            'address'  => 'required|string',
        ]);

        $seller = seller::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'age'      => $validated['age'],
            'address'  => $validated['address'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = auth('seller')->attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => 'Registered successfully',
            'token'   => $token,
        ]);
    }






    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
   

    public function me(Request $request)
    {
        auth()->shouldUse('seller');

        return response()->json([
            'token' => $request->header('Authorization'),
            'user' => auth('seller')->user()
        ]);
    }







    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('seller')->logout();
        return response()->json(['message' => 'Logged out successfully']);
    }


    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('seller')->refresh());
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
            'expires_in' => auth('seller')->factory()->getTTL() * 99999
        ]);
    }
}
