<?php

namespace App\Http\Controllers;

use App\Imports\OrderImport;
use App\Models\Order;
use App\Models\StatusOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
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
            "volume" => "required",
            "weight" => "required",
            "containt" => "required",
            "pieces" => "required"

        ]);

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
            "volume" => $request["volume"],
            "km" => (int)$request["km"] / 1000,
            "type_product" => $request["type_product"],
            "email" => $request["email"],
            "user_id" => $request->user()->id,
            "status_id" => 1,
            "moveType_id" => 1,
            "guide" => $this->generateUniqueCode()
        ]);

        StatusOrder::create([
            "order_id" => $data->id,
            "status_id" => 1
        ]);

        if ($data) {
            return response()->json(
                [
                    "message" => "La orden a sido creado de forma exitosa."
                ],
                200
            );
        } else {
            return response()->json(
                [
                    "message" => "Se ha presentado un error al crear la orden"
                ],
                400
            );
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
        if($request->user()->type_user == 2){
            if ($request["column"] == "created_at") {
                $data = Order::select("orders.*", "status.status")->where("user_id", $request->user()->id)->whereBetween('orders.created_at', [$request["start"] . ' 00:00:00', $request["end"] . ' 23:59:59'])
                ->join("status", "orders.status_id", "=", "status.id")->get();
            }
        }else{
            if ($request["column"] == "created_at") {
                $data = Order::select("orders.*", "status.status")->whereBetween('orders.created_at', [$request["start"] . ' 00:00:00', $request["end"] . ' 23:59:59'])
                ->join("status", "orders.status_id", "=", "status.id")->get();
            } elseif ($request["column"] == "status") {
                $data = Order::select("orders.*", "status.status")->whereDate("orders.created_at", Carbon::now())->whereIn('orders.status_id', [1, 5])
                ->join("status", "orders.status_id", "=", "status.id")->get();
            }
        }
        
        return response()->json($data);
    }

    public function printOrder(Request $request, $id)
    {

        $data = Order::where("id", $id)->first();
        $pdf = PDF::loadView('print_guide', array("data" => $data));

        return $pdf->download($data->guide . '.pdf');
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
            // $locations[$i]["km"] = floatval(number_format($this->distance("19.573382081680197", "-99.2023109033435", $locations[$i]["lat"], $locations[$i]["long"], "KM"), 2));
        }
        
        array_multisort(array_column($locations, "km"),SORT_ASC,$locations);

        $locations[0]["ind"] = 1;

        array_multisort(array_column($locations, "ind"),SORT_DESC,$locations);
        $index = 1;

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
                    $from = $lat_current.",".$long_current;
                    $to = $locations[$a]["lat"] . "," . $locations[$a]["long"];
                    $data = $this->calculate($from, $to);
                    $locations[$a]["km"] = $data->rows[0]->elements[0]->distance->value / 1000;
                    // $locations[$a]["km"] = floatval(number_format($this->distance($lat_current, $long_current, $locations[$a]["lat"], $locations[$a]["long"], "KM"), 2));
                }
                array_multisort(array_column($locations, "km"),SORT_ASC,$locations);

                for ($c=0; $c < count($locations); $c++) { 
                    $dataI = $locations[$c]["id"];
                    $exist = array_filter($dataArray, function($key) use($dataI){
                        if($key["id"] == $dataI){
                            return $key;
                        }
                    });

                    if(count($exist)){
                        $locations[$b]["ind"] = $index+1;
                        $dataArray[] = $locations[$b];
                        unset($locations[$b]);
                        break;
                    }else{
                        if($locations[$c]["moveType"] == 2 && $locations[$c]["status"] == 1){
                            continue;
                        } else{
                            $locations[$b]["ind"] = $index+1;
                            $dataArray[] = $locations[$b];
                            unset($locations[$b]);
                            break;
                        }
                    }
                }
                
            }else{
                $locations[1]["ind"] = $index+1;
                $dataArray[] = $locations[1];
            }

            $cont--;
        }

        return $dataArray;
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
}
