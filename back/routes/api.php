<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SecredIdController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\StopController;
use App\Models\Order;
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


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/getInfo', [AuthController::class, 'getInfo'])->middleware('auth:sanctum');
Route::post('/secred_id', [SecredIdController::class, 'addSecred'])->middleware('auth:sanctum');
Route::post('/driver', [DriverController::class, 'add'])->middleware('auth:sanctum');
Route::get('/getDriver', [DriverController::class, 'getDriver'])->middleware('auth:sanctum');
Route::post('/order', [OrderController::class, 'add'])->middleware('auth:sanctum');
Route::post('/getOrder', [OrderController::class, 'getOrder'])->middleware('auth:sanctum');
Route::get('/getClient', [ClientController::class, 'getClients'])->middleware('auth:sanctum');
Route::post('/traceRoute', [OrderController::class, 'trace_route'])->middleware('auth:sanctum');
Route::post('/asignedDriver', [StopController::class, 'add'])->middleware('auth:sanctum');
Route::post('/updateStatus', [StopController::class, 'updateStop'])->middleware('auth:sanctum');
Route::get('/disabledUser/{id}/{status}', [AuthController::class, 'disabledUser'])->middleware('auth:sanctum');
Route::post('/importOrders', [OrderController::class, 'importData'])->middleware('auth:sanctum');
Route::get('/stopsDriver/{imei}', [StopController::class, 'getStops']); //->middleware('auth:sanctum');
Route::get('/status/', [StatusController::class, 'getStatus']); //->middleware('auth:sanctum');
Route::get('/print_guide/{id}',[OrderController::class, 'printOrder']);
