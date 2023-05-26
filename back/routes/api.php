<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CancellationReasonController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConfKmController;
use App\Http\Controllers\ConfWeightController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\GroupCodeController;
use App\Http\Controllers\OpenPayController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SecredIdController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\StopController;
use App\Http\Controllers\ZipCodeController;
use App\Models\GroupCode;
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


Route::post('/register/client', [AuthController::class, 'register']);
Route::post('/update/client', [ClientController::class, 'edit']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AccountController::class, 'register']);
Route::post('/getInfo', [AuthController::class, 'getInfo'])->middleware('auth:sanctum');
Route::post('/secred_id', [SecredIdController::class, 'addSecred'])->middleware('auth:sanctum');
Route::post('/driver', [DriverController::class, 'add'])->middleware('auth:sanctum');
Route::get('/getDriver/{status}', [DriverController::class, 'getDriver'])->middleware('auth:sanctum');
Route::post('/order', [OrderController::class, 'add'])->middleware('auth:sanctum');
Route::get('/cloneOrder/{id}/{date}', [OrderController::class, 'clone'])->middleware('auth:sanctum');
Route::post('/getOrder', [OrderController::class, 'getOrder'])->middleware('auth:sanctum');
Route::get('/getClient/{status}', [ClientController::class, 'getClients'])->middleware('auth:sanctum');
Route::get('/getClientBy/{id}', [ClientController::class, 'getBy'])->middleware('auth:sanctum');
Route::post('/traceRoute', [OrderController::class, 'trace_route'])->middleware('auth:sanctum');
Route::post('/traceRouteDest', [OrderController::class, 'trace_route_destination'])->middleware('auth:sanctum');


Route::post('/asignedDriver', [StopController::class, 'add'])->middleware('auth:sanctum');
Route::post('/updateStatus', [StopController::class, 'updateStop'])->middleware('auth:sanctum');
Route::get('/disabledUser/{id}/{status}', [AuthController::class, 'disabledUser'])->middleware('auth:sanctum');
Route::post('/importOrders', [OrderController::class, 'importData'])->middleware('auth:sanctum');

Route::get('/status/', [StatusController::class, 'getStatus']); 
Route::get('/print_guide/{id}',[OrderController::class, 'printOrder']);

// driver

Route::get("/driver/{id}", [DriverController::class, 'getById']);
Route::get("/driver/uid/{uid}", [DriverController::class, 'getByUID']);
Route::post("/driver/update", [DriverController::class, 'update']);
Route::post("/driver/disabled", [DriverController::class, 'disabled']);

Route::get("/order/verifidDriver/{id}", [OrderController::class, 'validateOrderDriver']);
Route::get("/order/verifidAdmin/{id}", [OrderController::class, 'validateOrderAdmin']);
Route::get("/order/list/verifid", [OrderController::class, 'getOrdersForVerifiqued'])->middleware("auth:sanctum");
Route::get("/order/list/verifid/admin", [OrderController::class, 'getOrdersForVerifiquedAdmin'])->middleware("auth:sanctum");
Route::post("/order/update/verifid", [OrderController::class, 'updateStatusVerifiqued'])->middleware("auth:sanctum");


// paradas

Route::post('/finishStop', [StopController::class, 'finishStop']);
Route::post('/cancelStop', [StopController::class, 'cancelStop']);
Route::get('/stopsDriverFinish/{imei}', [StopController::class, 'getStopsFinish']); 
Route::get('/stopsDriver/{imei}', [StopController::class, 'getStops']);

// estados de cancelacion
Route::get('/reasons', [CancellationReasonController::class, 'getReason']);

Route::post('/cancelOrder', [OrderController::class, 'cancelOrder'])->middleware('auth:sanctum');
Route::get('/getByIdOrder/{id}', [StopController::class, 'getByIdOrder'])->middleware('auth:sanctum');

Route::post('/createOrder', [OrderController::class, 'apiOrder']);
Route::get('/orders', [OrderController::class, 'apiOrderGet']);

Route::post('/code/import', [ZipCodeController::class, 'import'])->middleware('auth:sanctum');
Route::get('/code', [ZipCodeController::class, 'getAll'])->middleware('auth:sanctum');
Route::get('/codePostal', [ZipCodeController::class, 'getAll']);
Route::get('/code/{code}', [ZipCodeController::class, 'getByCode'])->middleware('auth:sanctum');
Route::get('/codePostal/{code}', [ZipCodeController::class, 'getByCodeApi']);
Route::get('/code/delete/{id}', [ZipCodeController::class, 'delete'])->middleware('auth:sanctum');

Route::post('/payment', [OpenPayController::class, 'store'])->middleware('auth:sanctum');
Route::post('/balance', [BalanceController::class, 'getData'])->middleware('auth:sanctum');


// rutas
Route::post('/routes', [RouteController::class, 'list'])->middleware('auth:sanctum');
Route::post('/routes/asignedDriver', [RouteController::class, 'asignedDriver'])->middleware('auth:sanctum');
Route::get('/routes/{id}', [RouteController::class, 'getRoute'])->middleware('auth:sanctum');

// configuracion de precios

Route::post('/configuration/km', [ConfKmController::class, 'addUpdate']);
Route::get('/configuration/km/all', [ConfKmController::class, 'getAll']);
Route::get('/configuration/km/delete/{id}', [ConfKmController::class, 'delete']);
Route::post('/configuration/calculate', [ConfKmController::class, 'calculate']);


Route::post('/queryGuide', [OrderController::class, 'getOrderForGuide']);
Route::post('/configuration/weight', [ConfWeightController::class, 'addUpdate']);
Route::get('/configuration/weight/all', [ConfWeightController::class, 'getAll']);
Route::get('/configuration/weight/delete/{id}', [ConfWeightController::class, 'delete']);

Route::post('/generateCode', [AccountController::class, 'generateCode']);
Route::post('/sendRecovery', [AccountController::class, 'send_password']);
Route::post('/recoveryPassword', [AccountController::class, 'recovery_password']);


// group code

Route::get('/groupCode', [GroupCodeController::class, 'list'])->middleware("auth:sanctum");
Route::post('/groupCode/add', [GroupCodeController::class, 'add'])->middleware("auth:sanctum");
Route::post('/groupCode/update', [GroupCodeController::class, 'update'])->middleware("auth:sanctum");
Route::get('/groupCode/{id}/delete', [GroupCodeController::class, 'delete'])->middleware("auth:sanctum");


Route::get('/codesUni/', [GroupCodeController::class, 'getCodes'])->middleware("auth:sanctum");