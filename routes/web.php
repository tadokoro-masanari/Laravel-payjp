<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

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

//単発決済
Route::get('payment', [PaymentController::class, 'index'])->name('payment.index');
Route::post('payment', [PaymentController::class, 'payment'])->name('payment');
//定期課金
Route::get('payment/fixed', [PaymentController::class, 'fixed'])->name('payment.fixed');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
