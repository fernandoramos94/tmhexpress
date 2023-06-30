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
use stdClass;

class RouteController extends Controller
{
    public function list(Request $request)
    {
        $data = DB::select("select sum(fallidos) as fallidos, sum(entregados) as entregados, routes.*  from routes, JSON_TABLE(data, '$[*]' COLUMNS (fallidos INT PATH '$.fallido', entregados INT PATH '$.entregado')) as t group by id");
        foreach ($data as $item) {
            $item->data = json_decode($item->data);
            $item->driver = json_decode($item->driver);
        }

        $time_d = DB::table("time")->orderBy('id', "desc")->first();

        $t = date_create(date('Y-m-d H:i:s'));

        $difference = date_diff( $t, date_create($time_d->date_time)); 
        $minutes = $difference->days * 24 * 60;
        $minutes += $difference->h * 60;
        $minutes += $difference->i;

        return response()->json(["data"=>$data, "time" => $minutes]);
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

        $time_d = DB::table("time")->orderBy('id', "desc")->first();

        $t = date_create(date('Y-m-d H:i:s'));

        $difference = date_diff( $t, date_create($time_d->date_time)); 
        $minutes = $difference->days * 24 * 60;
        $minutes += $difference->h * 60;
        $minutes += $difference->i;

        return response()->json(["data"=>$data, "time" => $minutes]);
    }

    public function refresh()
    {
        $now = DB::raw('CURRENT_TIMESTAMP');
        DB::table("time")->insert(["date_time" => $now]);

        Route::where("process", 0)->update(["process" => 1]);

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
            $stop = Stops::select("id","photo_delivery","photo_cancellation","comment_cancellation","comment_delivery")->where("order_id", $item->order_id)->first();
            $item->order = $order;
            $item->stops = $stop;
        }

        $data->history = Log::where("route_id", $id)->get();

        return response()->json($data, 200);
    }

    public function reasignedZone(Request $request)
    {

        $orderC = new OrderController();

        $next = Route::find($request["id"]);
        $last = Route::find($request["id_last"]);
        $last_data = json_decode($last->data);
        $next_data = json_decode($next->data);
        $kms_last = 0;
        $hour_last = 0;

        foreach ($last_data as $key => $value) {
            if((int)$value->order_id == (int)$request["data"]["order_id"]){
                unset($last_data[$key]);
            }else{
                $kms_last += (float)$value->km;
                $hour_last += (int)$value->hour;
            }
        }

        $last->kms = $kms_last;
        $last->hours = $orderC->convertMin($hour_last);
        $endNext = end($next_data);

        $from = $endNext->lat . "," . $endNext->long; //direccion del ultimo registro
        $to = $request["data"]["lat"] . "," . $request["data"]["long"]; // direccion del nuevo registro

        $from = urlencode($from);
        $to = urlencode($to);

        $data = $orderC->calculate($from, $to);

        $next_km = $data->rows[0]->elements[0]->distance->value / 1000;
        $next_hour = $data->rows[0]->elements[0]->duration->value;

        $tmp = new stdClass;
        $tmp->km = (float)$next_km;
        $tmp->lat = $request["data"]["lat"];
        $tmp->hour = (int)$next_hour;
        $tmp->long = $request["data"]["long"];
        $tmp->address = $request["data"]["address"];
        $tmp->fallido = (int)$request["data"]["fallido"];
        $tmp->order_id = (int)$request["data"]["order_id"];
        $tmp->entregado = (int)$request["data"]["entregado"];

        $next_data[] = $tmp;

        $kms_next = 0;
        $hours_next = 0;
        foreach ($next_data as $value) {
            $kms_next += (float)$value->km;
            $hours_next += (int)$value->hour;
        }

        $next->hours = $orderC->convertMin($hours_next);
        $next->kms = $kms_next;

        $next->data = json_encode($next_data);
        $last->data = json_encode(array_values($last_data));

        Order::where("id", $request["data"]["order_id"])->update(["zone" => $next->zone]);

        Route::where("id", $request["id"])->update([
            "kms" => $next->kms,
            "stops" => count($next_data),
            "hours" => $next->hours,
            "data" => $next->data
        ]);
        Route::where("id", $request["id_last"])->update([
            "kms" => $last->kms,
            "stops" => count($last_data),
            "hours" => $last->hours,
            "data" => $last->data
        ]);

        return response()->json(["msg" => "Se ha reasignacioón de forma existosa"], 200);
    }
}
