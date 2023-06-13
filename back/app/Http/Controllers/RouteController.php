<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Log;
use App\Models\Order;
use App\Models\Route;
use App\Models\Stops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Storage;

class RouteController extends Controller
{
    public function list(Request $request)
    {
        $data = DB::select("select sum(fallidos) as fallidos, sum(entregados) as entregados, routes.*  from routes, JSON_TABLE(data, '$[*]' COLUMNS (fallidos INT PATH '$.fallido', entregados INT PATH '$.entregado')) as t group by id");
        foreach ($data as $item) {
            $item->data = json_decode($item->data);
            $item->driver = json_decode($item->driver);
        }

        return response()->json($data);
    }

    public function listRoute()
    {
        $data = Route::where("process", 0)->get();
        if(count($data) > 0){
            foreach ($data as $item) {
                $item->data = json_decode($item->data);
                $item->driver = json_decode($item->driver);
            }
    
        }
        return response()->json($data);
    }

    public function asignedDriver(Request $request){

        try {
            Route::where("id", $request["id"])->update(
                [
                    "driver" => json_encode($request["driver"]),
                    "reasign_driver" => 1
                ]
            );

            $route = Route::where("id", $request["id"])->first();

            $stop = new StopController();

            $stop->add($route, $request["reasign_driver"]);

            if($request["reasign_driver"] == 0){

                Log::insert([
                    "route_id" => $request["id"],
                    "user" => $request->user()->name,
                    "type_user" => 1,
                    "type" => "Asignación de conductor",
                    "msg" => "Realizado por ". $request->user()->name
                ]);
                return response()->json(["msg" => "El conductor ha sido asignado de forma exitosa.", "ok" => true], 200);
            }else{
                Log::insert([
                    "route_id" => $request["id"],
                    "user" => $request->user()->name,
                    "type_user" => 1,
                    "type" => "Reasignación de conductor",
                    "msg" => "Realizado por ". $request->user()->name
                ]);
                return response()->json(["msg" => "La reasignación del conductor ha sido de forma exitosa.", "ok" => true], 200);
            }
        } catch (Exception $e) {
            return response()->json(["msg" => $e->getMessage(), "ok" => false], 400);
        }
    }
    public function getRoute($id)
    {
        $data = collect(DB::select("select sum(fallidos) as fails, sum(entregados) as delivered, routes.*  from routes, JSON_TABLE(data, '$[*]' COLUMNS (fallidos INT PATH '$.fallido', entregados INT PATH '$.entregado')) as t where id= ".$id." group by id"))->first();
        $data->driver = json_decode($data->driver);
        $data->data = json_decode($data->data);
        $data->packages = count($data->data);
        $data->points = count($data->data);

        $data->driver = Driver::find($data->driver->id);

        foreach ($data->data as $item) {
            $order = Order::select("contact", "identification","phone", "email", 'zip_code')->where("id", $item->order_id)->first();
            $stop = Stops::select("photo_delivery","photo_cancellation","comment_cancellation","comment_delivery")->where("order_id", $item->order_id)->first();
            $item->order = $order;
            $item->stops = $stop;
        }

        $data->history = Log::where("route_id", $id)->get();

        return response()->json($data, 200);
    }
}
