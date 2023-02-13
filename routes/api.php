<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\RidesController;
use App\Http\Controllers\VehiclesController;
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

Route::post('/register', [AuthenticationController::class, 'register']);
Route::post('/login', [AuthenticationController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    Route::get('/rides', [RidesController::class, 'index']);
    Route::post('/rides', [RidesController::class, 'store']);
    Route::put('/rides/{id}', [RidesController::class, 'update']);
    Route::delete('/rides/{id}', [RidesController::class, 'delete']);
    Route::get('/stats/ranking', [RidesController::class, 'ranking']);
    Route::get('/stats/statement', [RidesController::class, 'statement']);

    Route::post('/search', [RidesController::class, 'search']);

    Route::get('/autocomplete', [RidesController::class, 'autocomplete']);

    Route::get('/vehicles/buses', [VehiclesController::class, 'indexBuses']);
    Route::get('/vehicles/trams', [VehiclesController::class, 'indexTrams']);
    Route::get('/vehicles/others', [VehiclesController::class, 'indexOthers']);
    Route::get('/vehicles/all', [VehiclesController::class, 'indexAllVehicles']);
    Route::get('/vehicles/depots', [VehiclesController::class, 'indexDepots']);
    Route::post('/vehicles', [VehiclesController::class, 'store']);
    Route::delete('/vehicles/{id}', [VehiclesController::class, 'delete']);

    Route::get('/users-management', [AuthenticationController::class, 'usersList']);
    Route::post('/users-management', [AuthenticationController::class, 'register']);
    Route::delete('/users-management/{id}', [AuthenticationController::class, 'delete']);

    Route::post('/change-password', [AuthenticationController::class, 'changePassword']);
    Route::post('/change-hints', [AuthenticationController::class, 'changeHints']);
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});
