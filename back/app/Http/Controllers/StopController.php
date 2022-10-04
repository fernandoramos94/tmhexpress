<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StatusOrder;
use App\Models\Stops;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class StopController extends Controller
{
    public function add(Request $request)
    {

        $query = Stops::where("driver_id", $request["driver"])->whereDate("date_order", Carbon::now())->orderBy("index", "desc")->limit(1)->first();

        $index = !(array)$query ? 0 : $query->index;
        
        try {
            foreach ($request["stops"] as $item) {
                Stops::create([
                    "order_id" => $item["id"],
                    "moveType_id" => $item["moveType"],
                    "driver_id" => $request["driver"],
                    "status_id" =>  $item["status"],
                    "date_order" => Carbon::now(),
                    "address" => $item["address"],
                    "lat" => $item["lat"],
                    "long" => $item["long"],
                    "index" => (int)$index + 1
                ]);

                Order::where("id", $item["id"])->update([
                    "status_id" => 2
                ]);

                StatusOrder::create([
                    "order_id" => $item["id"],
                    "status_id" => 2
                ]);

                // $this->sendEmail($item["id"]);

                $index++;
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
            // ->where([["order_id", "=", $id],["finish", "=", 1]])
            ->where([["order_id", "=", $id]])
            ->get();

        return response()->json($data);
    }

    public function getStops($imei)
    {

        $data = Stops::select("stops.*", "orders.contact", "orders.phone")->join("orders", "orders.id", "=", "stops.order_id")
            ->join("drivers", "stops.driver_id", "=", "drivers.id")
            ->where([["drivers.identification_phone", "=", $imei], ["finish", "=", 0]])
            ->whereDate("stops.date_order", Carbon::now())
            ->orderBy("index", "asc")->get();

        return response()->json(array("data" => $data), 200);
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

            $this->sendEmail($request["order_id"]);



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

        $disk = Storage::disk('gcs');
        $disk->put($name, file_get_contents($data));

        $disk->setVisibility($name, 'public');
        
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
