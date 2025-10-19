<?php

use App\Http\Controllers\KafkaTestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/kafka/publish', [KafkaTestController::class, 'publishMessage']);
Route::get('/kafka/publishBatch', [KafkaTestController::class, 'publishBatch']);
