<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\FlutterwavePaymentController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(CustomerController::class)->group(function () {
    Route::get('/v1/customers', 'index');
    Route::post('/v1/customers', 'save');
    Route::get('/v1/customers/{customerId}/payments', 'payment');
  
});

Route::controller(FlutterwavePaymentController::class)->group(function () {
    Route::post('/v1/charge-card', 'pay');
    Route::post('/v1/authorize-payment', 'authorizePayment');
    Route::post('/v1/validate-otp', 'validateOtp');
    Route::get('/v1/handle-callback', 'handleCallback');
});

Route::controller(PaymentController::class)->group(function () {
    Route::get('/v1/payments/customers/{customerId}', 'customerPayment');
});

