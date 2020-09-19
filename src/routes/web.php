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

Route::post('/laravel-monnify/webhook', [MonnifyController::class, 'webhook'])->name('monnify.webhook');

