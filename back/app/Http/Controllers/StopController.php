<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StatusOrder;
use App\Models\Stops;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StopController extends Controller
{
    public function add(Request $request)
    {
        
        try {
            foreach ($request["stops"] as $item) {
                Stops::create([
                    "order_id" => $item["id"],
                    "moveType_id" => $item["moveType"],
                    "driver_id" => $request["driver"],
                    "status_id" =>  $item["status"],
                    "date_order" => now(),
                    "index" => $request["ind"],
                    "address" => $item["address"],
                    "lat" => $item["lat"],
                    "long" => $item["long"]
                ]);
    
                Order::where("id", $item["id"])->update([
                    "status_id" => 2
                ]);

                StatusOrder::create([
                    "order_id" => $item["id"],
                    "status_id" => 2
                ]);
            }

            return response()->json(array(
                "message" => "Se han asignado las ordenes al driver de forma exitosa."
            ), 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(array(
                "message" => $e->getMessage()
            ), 400);
        }
       
    }
    public function updateStop(Request $request)
    {

        DB::beginTransaction();

        try {
            $moveType_id = 1;

            if($request["status_id"] >= 3){
                $moveType_id = 2;
            }
            Stops::where("id", $request["id"])->update([
                "status_id" => $request["status_id"],
                "moveType_id" => $moveType_id
            ]);

            StatusOrder::create([
                "order_id" => $request["order_id"],
                "status_id" => $request["status_id"]
            ]);

            return response()->json(array(
                "message" => "Cambio de estado realizado correctamente"
            ), 200);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(array(
                "message" => $th
            ), 401);
        }

        
    }

    public function getStops($imei)
    {

        $data = Stops::select("stops.*")->join("drivers", "stops.driver_id", "=", "drivers.id")->where("drivers.identification_phone" , $imei)->orderBy("index", "asc")->get();

        return response()->json(array("data" => $data), 200);
    }
}
