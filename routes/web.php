<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::controller(StripeController::class)->group(function () {
    Route::get('/thankyou', 'thankyou')->name('thankyou_page');
    Route::get('/failed', 'failed')->name('failed_page');
    Route::get('/success', 'success')->name('success_url');
    Route::get('/cancel', 'cancel')->name('cancel_url');
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});