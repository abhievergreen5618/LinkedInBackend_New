<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PostPerfectStripeController;

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

Route::controller(StripeController::class)->group(function () {
    Route::post('/create/session','createCheckoutSession')->name('create.session');
    Route::post('/customer-portal','customerPortal')->name('customer-portal');
});


Route::controller(PostPerfectStripeController::class)->group(function () {
    Route::post('/create/session/postperfect','createCheckoutSession')->name('create.session');
    Route::post('/customer-portal/postperfect','customerPortal')->name('customer-portal');
});
