<?php

namespace App\Http\Controllers;

use App\Imports\OrderImport;
use App\Models\Balance;
use App\Models\Order;
use App\Models\StatusOrder;
use App\Models\Stops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Validator;
use PDF;

class OrderController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            "contact" => "required",
            "identification" => "required",
            "phone" => "required",
            "destination_address" => "required",
            "origin_address" => "required",
            "type_product" => "required",
            "email" => "required",
            "height" => "required",
            "long" => "required",
            "wide" => "required",
            "weight" => "required",
            "containt" => "required",
            "pieces" => "required"

        ]);

        if(isset($request["price"])){
            $bal = Balance::where("user_id", $request->user()->id)->first();

            $total = count((array)$bal) > 0 ? (int)$bal->balance - (int)$request["price"] : 0;

            if(count((array)$bal) > 0 ){
                if($total <= 0){
                    return response()->json(["message" => "No cuentas con suficiente creditos para crear la orden, te recomendamos cargar tu cuenta para proceder con el proceso.", "ok" => false], 200);
                }else{
                    Balance::where("id", $bal->id)->update([
                        "balance" => $total
                    ]);
                }
                
            }else{
                return response()->json(["message" => "No cuentas con suficiente creditos para crear la orden, te recomendamos cargar tu cuenta para proceder con el proceso.", "ok" => false], 200);
            }
        }

        $data = Order::create([
            "contact" => $request["contact"],
            "identification" => $request["identification"],
            "destination_address" => $request["destination_address"],
            "phone" => $request["phone"],
            "pieces" => $request["pieces"],
            "origin_address" => $request["origin_address"],
            "containt" => $request["containt"],
            "long_destination" => $request["long_destination"],
            "lat_destination" => $request["lat_destination"],
            "long_origin" => $request["long_origin"],
            "lat_origin" => $request["lat_origin"],
            "weight" => $request["weight"],
            "long_" => $request["long"],
            "wide" => $request["wide"],
            "height" => $request["height"],
            "km" => (int)$request["km"] / 1000,
            "type_product" => $request["type_product"],
            "date_order" => $request["date_order"],
            "email" => $request["email"],
            "user_id" => $request->user()->id,
            "status_id" => 8,
            "moveType_id" => 1,
            "guide" => $this->generateUniqueCode()
        ]);

        StatusOrder::create([
            "order_id" => $data->id,
            "status_id" => 1
        ]);

        

        if ($data) {
            return response()->json( ["message" => "La orden a sido creada de forma exitosa.", "ok" => true], 200);
        } else {
            return response()->json(["message" => "Se ha presentado un error al crear la orden"], 400);
        }
    }

    public function clone($id, $date)
    {
        $order = Order::find($id);
        $neworder = $order->replicate();
        $neworder->created_at = Carbon::now();
        $neworder->date_order = $date;
        $neworder->status_id = 8;
        $save = $neworder->save();
        if($save){
            return response()->json( ["message" => "La orden a sido replicada de forma exitosa."], 200);
        } else {
            return response()->json(["message" => "Se ha presentado un error al replicar la orden"], 400);
        }
    }

    public function generateUniqueCode()
    {
        do {
            $code = random_int(100000, 999999);
        } while (Order::where("guide", "=", $code)->first());

        return $code;
    }

    public function getOrder(Request $request)
    {

        $data = [];
        if($request->user()->type_user == 2 || $request->user()->type_user == 3){
            if ($request["column"] == "created_at") {
                // $data = Order::select("orders.*", "status.status", "stops.photo_cancellation", "stops.photo_delivery","stops.photo_pickup", "stops.signature_delivery", "stops.signature_pickup")->where("user_id", $request->user()->id)
                // ->join("status", "orders.status_id", "=", "status.id")
                // ->leftJoin('stops', function($join){
                //     $join->on('orders.id', '=', 'stops.order_id')
                //     ->on('stops.moveType_id', '=', 'orders.moveType_id');
                // })->get();

                $data = DB::select("select orders.*, st.status, stops.photo_cancellation, stops.photo_delivery, stops.photo_pickup, stops.signature_delivery, stops.signature_pickup
                from orders
                inner join status as st on orders.status_id = st.id
                left join stops on orders.id = stops.order_id and stops.moveType_id = orders.moveType_id
                where orders.user_id = '".$request->user()->id."'");
            }
        }else{
            if ($request["column"] == "created_at") {

                $data = DB::select("select orders.*, st.status, stops.photo_cancellation, stops.photo_delivery, stops.photo_pickup, stops.signature_delivery, stops.signature_pickup
                from orders
                inner join status as st on orders.status_id = st.id
                left join stops on orders.id = stops.order_id and stops.moveType_id = orders.moveType_id");
                // $data = Order::select("orders.*", "status.status", "stops.photo_cancellation", "stops.photo_delivery","stops.photo_pickup", "stops.signature_delivery", "stops.signature_pickup")
                // ->join("status", "orders.status_id", "=", "status.id")
                // ->leftJoin('stops', function($join){
                //     $join->on('orders.id', '=', 'stops.order_id')
                //     ->on('stops.moveType_id', '=', 'orders.moveType_id');
                // })
                // ->toSql();
                // ->get();
            } elseif ($request["column"] == "status") {
                $data = Order::select("orders.*", "status.status")->whereIn('orders.status_id', [1, 5])
                ->join("status", "orders.status_id", "=", "status.id")->get();

                // $data = Order::select("orders.*", "status.status")->whereDate("orders.date_order", Carbon::now())->whereIn('orders.status_id', [1, 5])
                // ->join("status", "orders.status_id", "=", "status.id")->get();
            }
        }
        
        return response()->json($data);
    }

    public function getOrdersForVerifiqued()
    {
        $data = DB::select("select orders.*, users.name as client, st.status, stops.photo_cancellation, stops.photo_delivery, stops.photo_pickup, stops.signature_delivery, stops.signature_pickup
                from orders
                inner join status as st on orders.status_id = st.id
                inner join users on users.id = orders.user_id
                left join stops on orders.id = stops.order_id and stops.moveType_id = orders.moveType_id
                where orders.status_id = 8");

        return response()->json($data);
    }

    public function getOrdersForVerifiquedAdmin()
    {
        $data = DB::select("select orders.*, users.name as client, st.status, stops.photo_cancellation, stops.photo_delivery, stops.photo_pickup, stops.signature_delivery, stops.signature_pickup
                from orders
                inner join status as st on orders.status_id = st.id
                inner join users on users.id = orders.user_id
                left join stops on orders.id = stops.order_id and stops.moveType_id = orders.moveType_id
                where orders.status_id = 10");

        return response()->json($data);
    }
    public function updateStatusVerifiqued(Request $request)
    {

        $data = Order::whereIn("id", $request["data"])->update(
            ["status_id" => $request["status"]]
        );

        return response()->json([
            "msg" => "Proceso realizado de forma existosa."
        ], 200);
        
    }

    public function validateOrderDriver($id)
    {
        $data = Order::select("orders.*", "status.status")->where("guide", $id)->join("status", "orders.status_id", "=", "status.id")->first();

        if($data){
            if($data->status_id == 9){
                Order::where("id", $data->id)->update([
                    "status_id" => 10
                ]);
                return response()->json(
                    [
                        "ok" => true,
                        "msg" => "Orden verificada de forma exitosa"
                    ], 200
                );
            }else{
                return response()->json([
                    "ok" => false,
                    "msg" => "No he posible verificar la guia, estado actual(".$data->status.")"
                ], 200);
            }
        }else{
            return response()->json([
                "ok" => false,
                "msg" => "La orden que intenta consultar no se encuentra registrada en nuestra base de datos"
            ], 200);
        }

        
        
    }

    public function validateOrderAdmin($id)
    {
        $data = Order::select("orders.*", "status.status")->where("guide", $id)->join("status", "orders.status_id", "=", "status.id")->first();

        if($data){
            if($data->status_id == 10){
                Order::where("id", $data->id)->update([
                    "status_id" => 1
                ]);
                return response()->json(
                    [
                        "ok" => true,
                        "msg" => "Orden verificada de forma exitosa"
                    ], 200
                );
            }else{
                return response()->json([
                    "ok" => false,
                    "msg" => "No he posible verificar la guia, estado actual(".$data->status.")"
                ], 200);
            }
        }else{
            return response()->json([
                "ok" => false,
                "msg" => "La orden que intenta consultar no se encuentra registrada en nuestra base de datos"
            ], 200);
        }
        
    }

    public function printOrder(Request $request, $id)
    {

        $data = Order::where("id", $id)->first();
        $pdf = PDF::loadView('print_guide', array("data" => $data));

        return $pdf->download($data->guide . '.pdf');
    }

    public function downloadOrder(Request $request, $id)
    {

        $info = explode(",", $id);
        $data = Order::select("orders.*", "clients.name as client_name" , "clients.last_name as client_lastName", "clients.identification_number")
                ->whereIn("orders.id", $info)
                ->leftJoin("clients", "clients.user_id", "=", "orders.user_id")
                ->get();
        // print_r($data);
        $pdf = PDF::loadView('print_orders', array("data" => $data));

        return $pdf->download('listado_ordenes.pdf');
    }

    public function trace_route(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $pending = Order::select('id', 'origin_address as address', 'lat_origin as lat', 'long_origin as long',  DB::raw('"1" as status'), DB::raw('"1" as moveType'), DB::raw('"0" as ind'))
            ->where("status_id", 1)
            ->whereIn("id", $request["data"])
            ->get()->toArray();

        $destination = Order::select('id', 'destination_address as address', 'lat_destination as lat', 'long_destination as long',  DB::raw('"1" as status'), DB::raw('"2" as moveType'), DB::raw('"0" as ind'))
            ->where("status_id", 1)
            ->whereIn("id", $request["data"])
            ->get()->toArray();

        $pending_delivery =  Order::select('id', 'destination_address as address', 'lat_destination as lat', 'long_destination as long',  DB::raw('"5" as status'), DB::raw('"2" as moveType'), DB::raw('"0" as ind'))
            ->where("status_id", 5)
            ->whereIn("id", $request["data"])
            ->get()->toArray();

        $locations = array_merge($pending, $destination, $pending_delivery);

        for ($i = 0; $i < count($locations); $i++) {
            $from = "19.573382081680197, -99.2023109033435"; //direccion de thmexpress
            $to = $locations[$i]["lat"] . "," . $locations[$i]["long"];

            $from = urlencode($from);
            $to = urlencode($to);

            $data = $this->calculate($from, $to);
            $locations[$i]["km"] = $data->rows[0]->elements[0]->distance->value / 1000;
        }
        
        array_multisort(array_column($locations, "km"),SORT_ASC,$locations);

        $index = 0;

        $dataArray = [];
        for ($b=0; $b < count($locations); $b++) { 
            if($locations[$b]["moveType"] == 2 && $locations[$b]["status"] == 1){
                continue;
            }else{
                $dataArray[] = $locations[$b];
                unset($locations[$b]);
                break;
            }
        }
        $cont = count($locations);
        while ($cont != 0) {

            $lat_current = $dataArray[count($dataArray)-1]["lat"];
            $long_current = $dataArray[count($dataArray)-1]["long"];

            if(count($locations) > 1){
                for ($a=$index; $a < count($locations) ; $a++) { 
                    $locations = $this->resetKey($locations);
                    $from = $lat_current.",".$long_current;
                    $to = $locations[$a]["lat"] . "," . $locations[$a]["long"];
                    $data = $this->calculate($from, $to);
                    $locations[$a]["km"] = $data->rows[0]->elements[0]->distance->value / 1000;
                }
                array_multisort(array_column($locations, "km"),SORT_ASC,$locations);

                for ($c=0; $c < count($locations); $c++) { 
                    $locations = $this->resetKey($locations);
                    $dataI = $locations[$c]["id"];
                    $exist = array_filter($dataArray, function($key) use($dataI){
                        if($key["id"] == $dataI){
                            return $key;
                        }
                    });

                    if(count($exist)){
                        $locations = $this->resetKey($locations);
                        $dataArray[] = $locations[$c];
                        unset($locations[$c]);
                        break;
                    }else{
                        if($locations[$c]["moveType"] == 2 && $locations[$c]["status"] == 1){
                            continue;
                        } else{
                            $dataArray[] = $locations[$c];
                            unset($locations[$c]);
                            break;
                        }
                    }
                }
                
            }else{
                $dataArray[] = isset($locations[1]) ? $locations[1] : $locations[0];
            }

            $cont--;
        }

        return $dataArray;
    }

    public function resetKey($data){
        $retr = [];
        foreach ($data as $info) {
            $retr[] = $info;
        }

        return $retr;
    }

    public function trace_route_destination(Request $request)
    {
        date_default_timezone_set('America/Mexico_City');

        $destination = Order::select('id', 'destination_address as address', 'lat_destination as lat', 'long_destination as long',  DB::raw('"1" as status'), DB::raw('"2" as moveType'), DB::raw('"0" as ind'))
            ->where("status_id", 1)
            ->whereIn("id", $request["data"])
            ->get()->toArray();

        $locations =$destination;

        for ($i = 0; $i < count($locations); $i++) {
            $from = "19.573382081680197, -99.2023109033435"; //direccion de thmexpress
            $to = $locations[$i]["lat"] . "," . $locations[$i]["long"];

            $from = urlencode($from);
            $to = urlencode($to);

            $data = $this->calculate($from, $to);
            $locations[$i]["km"] = $data->rows[0]->elements[0]->distance->value / 1000;
        }

        return $locations;
    }


    public function calculate($from, $to)
    {
        $data = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?origins=$from&destinations=$to&language=es&sensor=false&key=AIzaSyBWIY8YFM3ckW2GxD-ATDeAq2TiHDDfteg");
        return json_decode($data);
    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

    public function importData(Request $request)
    {
        $file = $request->file("file");
        $resp = Excel::import(new OrderImport, $file);

        if($resp){
            return response()->json(["message" => "se han importado las ordenes de forma exitosa. "], 200);
        }else{
            return response()->json(["message" => "se ha presentado un error al importar las ordenes. "], 400);
        }
    }

    public function cancelOrder(Request $request)
    {
        $query = Order::where("id", $request["id"])->first();

        $stop = [];
        $moveType = null;
        if($request["type"] == "1"){ //cancelar recogida

            if(in_array($query->status_id, [1,2])){
                $stop = [
                    "status_id" => 6,
                    "finish" => 1,
                    "cancellationReason_id" => $request["cancellationReason_id"],
                    "comment_cancellation" => $request["comment_cancellation"]
                ];
                $moveType = 1;
            }
            else if($query->status_id == 3){
                return response()->json(["message" => "No es posible cancelar la orden, el pedido ya fue recolectado.", "icon" => "info"], 200);
            } else if($query->status_id == 6){
                return response()->json(["message" => "La orden ya se encuentra cancelada", "icon" => "info"]);
            }
        }else{ // cancelar entrega
            if(in_array($query->status_id, [1,2,3,5])){
                $stop = [
                    "status_id" => 7,
                    "finish" => 1,
                    "cancellationReason_id" => $request["cancellationReason_id"],
                    "comment_cancellation" => $request["comment_cancellation"]
                ];
                $moveType = 2;
            }else if($query->status_id == 4){
                return response()->json(["message" => "No es posible cancelar la orden, el pedido ya fue entregado.", "icon" => "info"], 200);
            }else if($query->status_id == 6){
                return response()->json(["message" => "La orden ya se encuentra cancelada", "icon" => "info"]);
            }
        }

        Order::where("id", $request["id"])->update(["status_id" => 7]);

        Stops::where([["order_id", "=", $request["id"]], ["moveType_id", "=", $moveType]])->update($stop);

        return response()->json(["message" => "La cancelacion se ha realizado de forma exitosa.", "icon" => "success"], 200);
    }
    public function apiOrder(Request $request)
    {
        $headers = $request->header();
        
        $authorization = $request->header('Authorization');

        if(!$authorization){
            return response()->json([
                "message" => "Cliente no autorizado para ejecutar la acción"
            ], 400);
        }
        $authorization = explode("Bearer ", $authorization);

        $id_client = DB::table("secred_id")->select("user_id", "token")->where("token", $authorization[1])->first();

        if(!$id_client){
            return response()->json(["message" => "Cliente no autorizado para ejecutar la acción"], 400);
        }

        Validator::make($request->all(), [
            'contact.indentification_number' => 'required',
            "contact.full_name" => "required",
            "contact.phone" => "required",
            "contact.email" => "required",
            "origin.address" => "required",
            "origin.latitude" => "required",
            "origin.longitude" => "required",
            "destination.address" => "required",
            "destination.latitude" => "required",
            "destination.longitude" => "required",
            "package.dimensions.pieces" => "required",
            "package.dimensions.weight" => "required",
            "package.product_type" => "required",
            "package.content" => "required"
        ])->validate();
        
        $data = Order::create([
            "contact" => $request["contact"]["full_name"],
            "identification" => $request["contact"]["indentification_number"],
            "destination_address" => $request["destination"]["address"],
            "phone" => $request["contact"]["phone"],
            "pieces" => $request["package"]["dimensions"]["pieces"],
            "origin_address" => $request["origin"]["address"],
            "containt" => $request["package"]["content"],
            "long_destination" => $request["destination"]["longitude"],
            "lat_destination" => $request["destination"]["latitude"],
            "long_origin" => $request["origin"]["longitude"],
            "lat_origin" => $request["origin"]["latitude"],
            "weight" => $request["package"]["dimensions"]["weight"],
            "wide" => 0,
            "height" => 0,
            "long_" => 0,
            "km" => 0,
            "type_product" => $request["package"]["product_type"],
            "date_order" => Carbon::now(),
            "email" => $request["contact"]["email"],
            "user_id" => $id_client->user_id,
            "status_id" => 1,
            "moveType_id" => 1,
            "guide" => $this->generateUniqueCode()
        ]);

        StatusOrder::create([
            "order_id" => $data->id,
            "status_id" => 1
        ]);

        if ($data) {
            return response()->json( [
                "order_id" => $data->id,
                "tracking_number" => $data->guide,
                "message" => "La orden a sido creada de forma exitosa."
            ], 200);
        } else {
            return response()->json(["message" => "Se ha presentado un error al crear la orden"], 400);
        }
        
    }

    public function apiOrderGet(Request $request)
    {
        $headers = $request->header();
        
        $authorization = $request->header('Authorization');

        if(!$authorization){
            return response()->json([
                "message" => "Cliente no autorizado para ejecutar la acción"
            ], 400);
        }
        $authorization = explode("Bearer ", $authorization);

        $id_client = DB::table("secred_id")->select("user_id", "token")->where("token", $authorization[1])->first();

        if(!$id_client){
            return response()->json(["message" => "Cliente no autorizado para ejecutar la acción"], 400);
        }

        $data = Order::select("orders.*", "status.status")->where('user_id', $id_client->user_id)->join("status", "orders.status_id", "=", "status.id")->get();

        return response()->json($data);
    }
    public function getOrderForGuide(Request $request)
    {
        $data = Order::select("orders.*", "status.status")->where('guide', $request["guide"])->join("status", "orders.status_id", "=", "status.id")->get();
        
        return response()->json($data, 200);
    }

    
}
