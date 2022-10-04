<?php

namespace App\Http\Controllers;

use App\Models\ConfKm;
use App\Models\ConfWeight;
use Illuminate\Http\Request;

class ConfKmController extends Controller
{
    public function addUpdate(Request $request)
    {
        if(isset($request["id"])){
            $data = ConfKm::where("id", $request["id"])->update([
                "min" => $request["min"],
                "max" => $request["max"],
                "price" => $request["price"],
                "price_max" => $request["price_max"]
            ]);
        }else{
            $data = ConfKm::insert([
                "min" => $request["min"],
                "max" => $request["max"],
                "price" => $request["price"],
                "price_max" => $request["price_max"]
            ]);
        }
 
        return response()->json(["message" => "Proceso realizado con exito." ], 200);
    }

    public function getAll()
    {
        $data = ConfKm::all();

        return response()->json($data, 200);
    }

    public function delete($id)
    {
        ConfKm::where("id", $id)->delete();

        return response()->json(["message" => "Proceso realizado con exito"], 200);
    }

    public function calculate(Request $request)
    {
        $km = ConfKm::first();

        $price_km = 0;

        if(count((array)$km) > 0){
            $kms = (float)$request["km"] / 1000;
            if($kms <= $km->min){
                $price_km = (float)$km->price;
            }else if($kms > $km->min && $kms <= $km->max){
                $calc = ceil($kms) * (float)$km->price;
                $price_km = $calc;
            }else{
                $price_km = (float)$km->price_max;
            }
        }

        $weight = ConfWeight::first();

        $price_weight = 0;

        if(count((array)$weight) > 0){

            $cub = (float)$request["wide"] * (float)$request["long"] * (float)$request["height"];
            $cub = $cub / 5000;

            $pes = (float)$request["weight"] > $cub ? (float)$request["weight"]: $cub;
            
            if($pes <= $weight->min){
                $price_weight = (float)$weight->price;
            }else if($pes > $weight->min && $pes <= $weight->max){
                $calc = ceil($pes) * (float)$weight->price;
                $price_weight = $calc;
            }else{
                $price_weight = (float)$weight->price_max;
            }
        }

        $total = $price_km + $price_weight;

        return response()->json($total, 200);
    }
}
