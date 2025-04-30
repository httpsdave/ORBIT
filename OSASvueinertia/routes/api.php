// routes/api.php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/events', [EventController::class, 'store']);
Route::get('/events', [EventController::class, 'getEvents']);
Route::put('/events/{event}', [EventController::class, 'update']);
Route::delete('/events/{event}', [EventController::class, 'destroy']);
Route::post('/extract-event-info', [EventController::class, 'extractEventInfo']);