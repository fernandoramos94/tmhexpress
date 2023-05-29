<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Order;
use App\Models\Route;
use App\Models\StatusOrder;
use App\Models\Stops;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class StopController extends Controller
{
    public function add($data, $resign_driver)
    {

        try {

            $driver = json_decode($data->driver);
            $query = Stops::where("driver_id", $driver->id)->whereDate("date_order", Carbon::now())->orderBy("index", "desc")->limit(1)->first();
            $index = !(array)$query ? 0 : $query->index;

            $stops = json_decode($data->data);

            

            foreach ($stops as $item) {
                if($resign_driver == false){
                    Stops::create([
                        "order_id" => $item->order_id,
                        "moveType_id" => 2,
                        "driver_id" => $driver->id,
                        "status_id" => 2,
                        "date_order" => Carbon::now(),
                        "address" => $item->address,
                        "lat" => $item->lat,
                        "long" => $item->long,
                        "index" => (int)$index + 1
                    ]);
    
                    Order::where("id", $item->order_id)->update([
                        "status_id" => 2
                    ]);
    
                    StatusOrder::create([
                        "order_id" => $item->order_id,
                        "status_id" => 2
                    ]);
    
                    $index++;
                }else{
                    Stops::where("order_id", $item->order_id)->update([
                        "driver_id" => $driver->id
                    ]);
                }
                
            }

            return response()->json(array("message" => "Se han asignado las ordenes al driver de forma exitosa."), 200);
        } catch (Exception $e) {
            return response()->json(array(
                "message" => $e->getMessage()
            ), 400);
        }
    }

    public function getByIdOrder($id)
    {
        $data = Stops::select("stops.*", "move_type.moving", "status.status", "cancellation_reason.reason")
            ->join("move_type", "stops.moveType_id", "=", "move_type.id")
            ->join("status", "stops.status_id", "=", "status.id")
            ->leftjoin("cancellation_reason", "stops.cancellationReason_id", "=", "cancellation_reason.id")
            ->where([["order_id", "=", $id]])
            ->get();

        return response()->json($data);
    }

    public function getStops($imei)
    {


        $data = DB::select("
            select stops.*, orders.contact, orders.phone, orders.zip_code, routes.id as route_id from stops 
            inner join orders on orders.id = stops.order_id 
            inner join drivers on stops.driver_id = drivers.id 
            LEFT JOIN routes on json_contains(data->'$[*].order_id', json_array(orders.id))
            where (drivers.identification_phone = '".$imei."' and finish = 0) and date(stops.date_order) = '".Carbon::now()->toDateString()."' order by stops.index asc
        ");

        return response()->json(array("data" => $data, "orwr" => Carbon::now()), 200);
    }

    public function getStopsFinish($imei)
    {
        $data = Stops::select("stops.*", "orders.contact", "orders.phone")->join("orders", "orders.id", "=", "stops.order_id")
            ->join("drivers", "stops.driver_id", "=", "drivers.id")
            ->where([["drivers.identification_phone", "=", $imei], ["finish", "=", 1]])
            ->whereDate("stops.date_order", Carbon::now())
            ->orderBy("index", "asc")->get();

        return response()->json(array("data" => $data), 200);
    }

    public function finishStop(Request $request)
    {

        try {
            $data = [];

            if(!is_null($request["signature"])){
                $signature = $this->createImageFromBase64($request["signature"], '1'.date('YmdHisu'));
            }
            if(!is_null($request["evidence"])){
                
                $evidence = $this->createImageFromBase64($request["evidence"], '2'.date('YmdHisu'));
            }
                        
            if ($request["type"] == "pickup") {

                $data = [
                    "status_id" => 3,
                    "finish" => 1
                ];
                if(!is_null($request["evidence"])){
                    $data += [ "photo_pickup" => $evidence ];
                }
                if(!is_null($request["signature"])){
                    $data += [ "signature_pickup" => $signature ];
                }
                if(!is_null($request["comment"])){
                    $data += [ "comment_pickup" => $request["comment"] ];
                }

                Stops::where("id", $request["id"])->update($data);

                StatusOrder::create([
                    "order_id" => $request["order_id"],
                    "status_id" => 3
                ]);

                Order::where("id", $request["order_id"])->update(["status_id" => 3]);
            }else{
                $data = [
                    "status_id" => 4,
                    "finish" => 1
                ];
                if(!is_null($request["evidence"])){
                    $data += [ "photo_delivery" => $evidence ];
                }
                if(!is_null($request["signature"])){
                    $data += [ "signature_delivery" => $signature ];
                }
                if(!is_null($request["comment"])){
                    $data += [ "comment_delivery" => $request["comment"] ];
                }

                Stops::where("order_id", $request["order_id"])->update($data);

                StatusOrder::create([
                    "order_id" => $request["order_id"],
                    "status_id" => 4
                ]);

                Order::where("id", $request["order_id"])->update(["status_id" => 4]);
            }

            $route = Route::find($request["route_id"]);

            $orders = json_decode($route->data);
            $driver = json_decode($route->driver);
            $counts = count($orders);
            $execute = 0;
            foreach ($orders as $item) {
                if($item->order_id == $request["order_id"]){
                    $item->entregado = 1;

                    $execute += 1;
                }

                if((int)$item->entregado > 0 || (int)$item->fallido > 0 ){
                    $execute += 1;
                }
            }

            Route::where("id", $request["route_id"])->update([
                "data" => json_encode($orders)
            ]);

            if($counts == $execute){
                Log::insert([
                    "route_id" => $request["route_id"],
                    "user" => $driver->name,
                    "type_user" => 2,
                    "type" => "Finalizado",
                    "msg" => "Realizado por ". $driver->name
                ]);
            }

            

            // $this->sendEmail($request["order_id"]);



            return response()->json(array("message" => "Proceso realizado con exito"), 200);
        } catch (Exception $e) {
            return response()->json(array(
                "message" => $e->getMessage()
            ), 400);
        }
    }

    public function createImageFromBase64($data, $na)
    {

        $name = $na.".". explode('/', explode(':', substr($data, 0, strpos($data, ';')))[1])[1];

        $base64_str = substr($data, strpos($data, ",")+1);

        $image = base64_decode($base64_str);

        $disk = Storage::disk('local');
        $disk->put($name, $image);
        
        $url = $disk->url($name);

        return $url;
    }

    public function cancelStop(Request $request)
    {

        try {

            if(!is_null($request["evidence"])){
                $evidence = $this->createImageFromBase64($request["evidence"], '2'.date('YmdHisu'));
            }

            $data = [
                "status_id" => 6,
                "finish" => 1,
                "cancellationReason_id" => $request["cancellationReason_id"]
            ];

            if(!is_null($request["evidence"])){
                $data += [ "photo_cancellation" => $evidence ];
            }
            if(!is_null($request["comment"])){
                $data += [ "comment_cancellation" => $request["comment"] ];
            }

            Stops::where("order_id", $request["order_id"])->update($data);

            StatusOrder::create([
                "order_id" => $request["order_id"],
                "status_id" => 6
            ]);

            Stops::where("order_id", $request["order_id"])->update(["finish" => 1]);

            Order::where("id", $request["order_id"])->update(["status_id" => 6]);


            $route = Route::find($request["route_id"]);

            $orders = json_decode($route->data);
            $driver = json_decode($route->driver);
            $counts = count($orders);
            $execute = 0;
            foreach ($orders as $item) {
                if($item->order_id == $request["order_id"]){
                    $item->fallido = 1;

                    $execute += 1;
                }

                if((int)$item->entregado > 0 || (int)$item->fallido > 0 ){
                    $execute += 1;
                }
            }

            Route::where("id", $request["route_id"])->update([
                "data" => json_encode($orders)
            ]);

            if($counts == $execute){
                Log::insert([
                    "route_id" => $request["route_id"],
                    "user" => $driver->name,
                    "type_user" => 2,
                    "type" => "Finalizado",
                    "msg" => "Realizado por ". $driver->name
                ]);
            }

            return response()->json([
                "message" => "Proceso realizado con existo"
            ], 200);

        } catch (Exception $e) {
            return response()->json(array(
                "message" => $e->getMessage()
            ), 400);
        }
    }

    public function sendEmail($id)
    {
        $data = Order::select(
            "orders.contact",
            "orders.email",
            "orders.destination_address",
            "orders.guide",
            "orders.status_id",
            "stops.updated_at",
            "stops.photo_delivery",
            "drivers.name",
            "drivers.last_name",
        )->join("stops", "orders.id", "=", "stops.order_id")
        ->join("drivers", "stops.driver_id", "=", "drivers.id")
        ->where("orders.id",$id)->first();

        $email = $data->email;
        $client = $data->contact;

        $template = $data->status_id == 3 ? "mail.route" : "mail.delivered";
        $text_template = $data->status_id == 3 ? "Tu pedido va en camino" : "Tu pedido ha sido entregado";

        Mail::send($template, ["data" => $data], function ($message) use ($email, $client, $text_template) {
            $message->to($email, $client)
                ->subject($text_template);
            $message->from("no-reply@tmhexpress.com", "Tmh Express");
        });
    }
        
}
