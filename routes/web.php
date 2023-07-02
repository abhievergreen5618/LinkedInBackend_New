<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\PostPerfectStripeController;

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
})->name("home");


Route::controller(StripeController::class)->group(function () {
    Route::get('/thankyou', 'thankyou')->name('thankyou_page');
    Route::get('/failed', 'failed')->name('failed_page');
    Route::get('/success', 'success')->name('success_url');
    Route::get('/cancel', 'cancel')->name('cancel_url');
});

Route::controller(PostPerfectStripeController::class)->group(function () {
    Route::get('/postperfect/thankyou', 'thankyou')->name('postperfect_thankyou_page');
    Route::get('/postperfect/failed', 'failed')->name('postperfect_failed_page');
    Route::get('/postperfect/success', 'success')->name('postperfect_success_url');
    Route::get('/postperfect/cancel', 'cancel')->name('postperfect_cancel_url');
});


Route::controller(StripeWebhookController::class)->group(function () {
    Route::post('/stripe/webhook', 'handleWebhook')->name('stripe.webhook');
    Route::get('/stripe/webhook/test', 'replayWebhook')->name('stripe.webhook.test');
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});
