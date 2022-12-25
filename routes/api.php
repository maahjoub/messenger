<?php

use App\Http\Controllers\ConversationsController;
use App\Http\Controllers\MeassgesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::middleware('auth:sanctum')->group(function() {
    Route::get('conv', [ConversationsController::class, 'index']);
    Route::get('conv/{id}/msg', [MeassgesController::class, 'index']);
    Route::post('message', [MeassgesController::class, 'store'])->name('api.meeseges.store');
    Route::delete('msg/{id}', [MeassgesController::class, 'destroy']);
});
