<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WristbandController;
use App\Http\Controllers\MedicalController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProductSelectedController;
use App\Http\Controllers\ProductRestrictonsController;
use App\Http\Controllers\NotificationsController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});





Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});


//parent routes
Route::prefix('parent')->group(function () {

    Route::post('register', [ParentController::class, 'register']);
    Route::post('login',    [ParentController::class, 'login']);

    // ✅ me
    Route::post('me', function (Request $request) {
        return response()->json([
            'user' => auth('parent')->user(),
        ]);
    });

    // ✅ logout
    Route::post('logout', function (Request $request) {
        try {
            auth('parent')->logout();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to logout'], 500);
        }
    });

    // ✅ refresh
    Route::post('refresh', function (Request $request) {
        try {
            $newToken = auth('parent')->refresh();
            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => auth('parent')->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to refresh token'], 401);
        }
    });

});


//seller routes
Route::prefix('seller')->group(function () {

    Route::post('register', [SellerController::class, 'register']);
    Route::post('login',    [SellerController::class, 'login']);

    // ✅ me
    Route::post('me', function (Request $request) {
        return response()->json([
            'user' => auth('seller')->user(),
        ]);
    });

    // ✅ logout
    Route::post('logout', function (Request $request) {
        try {
            auth('seller')->logout();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to logout'], 500);
        }
    });

    // ✅ refresh
    Route::post('refresh', function (Request $request) {
        try {
            $newToken = auth('seller')->refresh();
            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => auth('seller')->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to refresh token'], 401);
        }
    });

});




//Admin API
Route::prefix('admin')->group(function () {

    Route::post('register', [AdminController::class, 'register']);
    Route::post('login',    [AdminController::class, 'login']);

    // ✅ me
    Route::post('me', function (Request $request) {
        return response()->json([
            'user' => auth('admin')->user(),
        ]);
    });

    // ✅ logout
    Route::post('logout', function (Request $request) {
        try {
            auth('admin')->logout();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to logout'], 500);
        }
    });

    // ✅ refresh
    Route::post('refresh', function (Request $request) {
        try {
            $newToken = auth('admin')->refresh();
            return response()->json([
                'access_token' => $newToken,
                'token_type'   => 'bearer',
                'expires_in'   => auth('admin')->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to refresh token'], 401);
        }
    });

});


// student api
Route::group([
    'middleware' => ['api','admin_or_parent'],
    'prefix' => 'student',
], function () {
    Route::get('index', [StudentController::class, 'index']);
    Route::post('store', [StudentController::class, 'store']);
    Route::get('show/{id}', [StudentController::class, 'show']);
    Route::get('show_StuCode/{id}', [StudentController::class, 'show_StuCode']);
    Route::post('update/{id}', [StudentController::class, 'update']);
    Route::delete('destroy/{id}', [StudentController::class, 'destroy']);
});


// Product api
Route::group([
    'middleware' => 'api',
    'prefix' => 'product',
], function () {
    Route::get('index', [ProductController::class, 'index']);
    Route::post('store', [ProductController::class, 'store']);
    Route::get('show/{id}', [ProductController::class, 'show']);
    Route::get('show_name/{id}', [ProductController::class, 'show_name']);
    Route::post('update/{id}', [ProductController::class, 'update']);
    Route::delete('destroy/{id}', [ProductController::class, 'destroy']);
});

// Product Selected api
Route::group([
    'middleware' => ['api','auth:admin'],
    'prefix' => 'product_selected',
], function () {
    Route::get('index', [ProductSelectedController::class, 'index']);
    Route::post('store', [ProductSelectedController::class, 'store']);
    Route::get('show/{id}', [ProductSelectedController::class, 'show']);
    Route::get('show_stuCode/{id}', [ProductSelectedController::class, 'show_stuCode']);
    Route::post('update/{id}', [ProductSelectedController::class, 'update']);
    Route::delete('destroy/{id}', [ProductSelectedController::class, 'destroy']);
});

// Product Restriction api
Route::group([
    'middleware' => 'api',
    'prefix' => 'product_restriction',
], function () {
    Route::get('index', [ProductRestrictonsController::class, 'index']);
    Route::get('show/{id}', [ProductRestrictonsController::class, 'show']);
    Route::get('show_stuCode/{id}', [ProductRestrictonsController::class, 'show_stuCode']);
    Route::post('update/{id}', [ProductRestrictonsController::class, 'update']);
    Route::delete('destroy/{id}', [ProductRestrictonsController::class, 'destroy']);
});

// Wristband api
Route::group([
    'middleware' => 'api',
    'prefix' => 'wristband',
], function () {
    Route::get('index', [WristbandController::class, 'index']);
    Route::post('store', [WristbandController::class, 'store']);
    Route::get('show/{id}', [WristbandController::class, 'show']);
    Route::get('show_StuCode/{id}', [WristbandController::class, 'show_StuCode']);
    Route::post('update/{id}', [WristbandController::class, 'update']);
    Route::delete('destroy/{id}', [WristbandController::class, 'destroy']);
});

// medical api
Route::group([
    'middleware' => 'api',
    'prefix' => 'medical',
], function () {
    Route::get('index', [MedicalController::class, 'index']);
    Route::post('store', [MedicalController::class, 'store']);
    Route::get('show/{id}', [MedicalController::class, 'show']);
    Route::get('show_StuCode/{id}', [MedicalController::class, 'show_StuCode']);
    Route::post('update/{id}', [MedicalController::class, 'update']);
    Route::delete('destroy/{id}', [MedicalController::class, 'destroy']);
});

// notification api
Route::group([
    'middleware' => 'api',
    'prefix' => 'notification',
], function () {
    Route::get('index', [NotificationsController::class, 'index']);
    Route::get('show/{id}', [NotificationsController::class, 'show']);
    Route::get('show_stuCode/{id}', [NotificationsController::class, 'show_stuCode']);
    // Route::post('update/{id}', [NotificationsController::class, 'update']);
    Route::delete('destroy/{id}', [NotificationsController::class, 'destroy']);
});