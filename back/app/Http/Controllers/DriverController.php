<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            "identification_type" => "required",
            "identification_number" => "required",
            "name" => "required",
            "last_name" => "required",
            "identification_phone" => "required",
            "license_plate" => "required"
        ]);

        $data = Driver::create([
            "identification_type" => $request["identification_type"],
            "identification_number" => $request["identification_number"],
            "name" => $request["name"],
            "last_name" => $request["last_name"],
            "identification_phone" => $request["identification_phone"],
            "license_plate" => $request["license_plate"]
        ]);

        if($data){
            return response()->json([
                "message" => "Se ha agregado el registro de forma existosa"
            ], 200);
        }else{
            return response()->json([
                "message" => "Se ha presentado un error."
            ], 401);
        }
    }

    public function getDriver(Request $request)
    {
        $data = Driver::all();
        return response()->json($data);
    }
}
