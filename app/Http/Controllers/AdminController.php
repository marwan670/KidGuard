<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterAdmin;
use App\Models\admin;
use Illuminate\Http\Request;
use App\Events\verfiyEmailAddressCode;

class AdminController extends Controller
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

    public function resendActiveCode(Request $request)
    {
        $data = $request->validate(['code_type' => 'required|in:email,phone']);
        if ($request->code_type == 'phone') {
            //
        } else if ($request->code_type == 'email') {
            event(new verfiyEmailAddressCode(parents::find(auth()->admin()->id)));
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('admin')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return res_data(__('main.msg_login'), $this->respondWithToken($token));
    }
    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6'
        ]);

        $admin = admin::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = auth('admin')->attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => 'Registered successfully',
            'token'   => $token,
        ]);
    }


    /**
     * Get the authenticated admin.
     *
     * @return \Illuminate\Http\JsonResponse
     */
     public function me()
    {
        return res_data(
            __('main.meg_me'),
            response()->json(auth('admin')->user()->only('id', 'name', 'email'))
        );
    }


    /**
     * Log the admin out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

        auth('admin')->logout();
        // return response()->json(['message' => 'Successfully logged out']);
        return res_data(__('main.msg_logout'), []);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('admin')->refresh());
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
            'expires_in' => auth('admin')->factory()->getTTL() * 99999
        ]);
    }
}
