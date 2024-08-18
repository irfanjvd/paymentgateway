<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    // return $request->user();
// });

Route::post('signup',[ApiController::class,'signup'])->name('signup');
Route::post('login',[ApiController::class,'login'])->name('login');
Route::middleware('auth:api')->post('change_password',[ApiController::class,'changePassword'])->name('change_password');
Route::middleware('auth:api')->any('get_subscriptions',[ApiController::class,'getSubscriptions'])->name('get_subscriptions');
Route::middleware('auth:api')->any('get_checkout_session',[ApiController::class,'getCheckoutSession'])->name('get_checkout_session');
Route::middleware('auth:api')->any('check_user_have_subscription',[ApiController::class,'checkUserHaveSubscription'])->name('check_user_have_subscription');
Route::middleware('auth:api')->any('cancel_subscription',[ApiController::class,'cancelSubscription'])->name('cancel_subscription');



Route::middleware('auth:api')->any('get_user_subscription',[ApiController::class,'getUserSubscription'])->name('get_user_subscription');
Route::post('reset_password',[ApiController::class,'resetPassword'])->name('reset_password');
Route::post('verify_email',[ApiController::class,'verifyEmail'])->name('verify_email');
Route::post('forgot_password',[ApiController::class,'forgotPassword'])->name('forgot_password');
