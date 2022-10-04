<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\LogPayment;
use Illuminate\Http\Request;
use Openpay\Data\Openpay;
use Exception;
use Openpay\Data\OpenpayApiError;
use Openpay\Data\OpenpayApiAuthError;
use Openpay\Data\OpenpayApiRequestError;
use Openpay\Data\OpenpayApiConnectionError;
use Openpay\Data\OpenpayApiTransactionError;

use function PHPUnit\Framework\isEmpty;

require_once '../vendor/autoload.php';

class OpenPayController extends Controller
{
    public function store(Request $request)
    {
        try {
            $openpay = Openpay::getInstance(env('OPENPAY_ID'), env('OPENPAY_SK'));
            
            Openpay::setProductionMode(env('OPENPAY_PRODUCTION_MODE'));
            
            // create object customer
            $customer = array(
                'name' => $request->user()->name,
                'last_name' => $request->user()->last_name,
                'email' => $request->user()->email
            ); 

            // create object charge
            $chargeRequest =  array(
                'method' => 'card',
                'source_id' => $request["token"],
                'amount' => (int)$request["price"],
                'currency' => 'MXN',
                'description' => 'Recarga credito desde plataforma TMH Express',
                'device_session_id' => $request["deviceSessionId"],
                'customer' => $customer
            );

            $charge = $openpay->charges->create($chargeRequest);

            $bal = Balance::where("user_id", $request->user()->id)->first();

            $total = count((array)$bal) > 0 ? (int)$bal->balance + (int)$request["price"] : (int)$request["price"];

            if(count((array)$bal) > 0 ){
                Balance::where("id", $bal->id)->update([
                    "balance" => $total
                ]);
            }else{
                Balance::insert([
                    "balance" => $total,
                    "user_id" => $request->user()->id
                ]);
            }

            LogPayment::insert([
                "log" => json_encode($charge),
                "user_id" => $request->user()->id
            ]);

            return response()->json(
                ["data" => $charge], 200);


        } catch (OpenpayApiTransactionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], 400);
        } catch (OpenpayApiRequestError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], 400);
        } catch (OpenpayApiConnectionError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], 400);
        } catch (OpenpayApiAuthError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], 400);
        } catch (OpenpayApiError $e) {
            return response()->json([
                'error' => [
                    'category' => $e->getCategory(),
                    'error_code' => $e->getErrorCode(),
                    'description' => $e->getMessage(),
                    'http_code' => $e->getHttpCode(),
                    'request_id' => $e->getRequestId()
                ]
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => [
                    'description' => $e->getMessage()
                ]
            ], 400);
        }
    }
}
