<?php

namespace App\Http\Controllers;

use App\Models\ConfKm;
use App\Models\ConfWeight;
use Illuminate\Http\Request;

class ConfWeightController extends Controller
{
    public function addUpdate(Request $request)
    {
        if(isset($request["id"])){
            $data = ConfWeight::where("id", $request["id"])->update([
                "min" => $request["min"],
                "max" => $request["max"],
                "price" => $request["price"],
                "price_max" => $request["price_max"]
            ]);
        }else{
            $data = ConfWeight::insert([
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
        $data = ConfWeight::all();

        return response()->json($data, 200);
    }

    public function delete($id)
    {
        ConfWeight::where("id", $id)->delete();

        return response()->json(["message" => "Proceso realizado con exito"], 200);
    }

}
