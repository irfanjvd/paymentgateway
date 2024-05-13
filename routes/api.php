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
Route::post('verify_email',[ApiController::class,'verifyEmail'])->name('verify_email');
Route::post('forgot_password',[ApiController::class,'forgotPassword'])->name('forgot_password');
