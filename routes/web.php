<?php
/**
 * Created By: Henry Ejemuta
 * PC: Enrico Systems
 * Project: laravel-monnify
 * Class Name: web.php
 * Date Created: 9/11/20
 * Time Created: 8:53 AM
 */


use Illuminate\Support\Facades\Route;
use HenryEjemuta\LaravelMonnify\Http\Controllers\MonnifyController;

Route::prefix('laravel-monnify/webhook')->group(function () {
    Route::post('transaction-completion', [MonnifyController::class, 'txnCompletion'])->name('monnify.webhook.transaction-completion');
    Route::post('refund-completion', [MonnifyController::class, 'refundCompletion'])->name('monnify.webhook.refund-completion');
    Route::post('disbursement', [MonnifyController::class, 'disbursement'])->name('monnify.webhook.disbursement');
    Route::post('settlement', [MonnifyController::class, 'settlement'])->name('monnify.webhook.settlement');

    Route::post('', [MonnifyController::class, 'webhook'])->name('monnify.webhook');
});
