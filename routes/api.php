<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/payments/',[PaymentController::class,'createInvoice']);
Route::post('/payments/webhook',[PaymentController::class,'handleWebhook']);