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
            "license_plate" => "required",
            "type" => "required"
        ]);

        $data = Driver::create([
            "identification_type" => $request["identification_type"],
            "identification_number" => $request["identification_number"],
            "name" => $request["name"],
            "last_name" => $request["last_name"],
            "identification_phone" => $request["identification_phone"],
            "license_plate" => $request["license_plate"],
            "type" => $request["type"]
        ]);

        if($data){
            return response()->json([
                "message" => "Se ha agregado el registro de forma existosa"
            ], 200);
        }else{
            return response()->json([
                "message" => "Se ha presentado un error."
            ], 400);
        }
    }

    public function getDriver($status)
    {
        $data = Driver::where("status", $status)->get();
        return response()->json($data);
    }

    public function getById($id)
    {
        $data = Driver::where("id", $id)->first();

        return response()->json($data, 200);
    }

    public function getByUID($uid)
    {
        $data = Driver::where("identification_phone", $uid)->first();

        return response()->json($data, 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            "identification_type" => "required",
            "identification_number" => "required",
            "name" => "required",
            "last_name" => "required",
            "identification_phone" => "required",
            "license_plate" => "required",
            "type" => "required"
        ]);

        $data = Driver::where("id", $request["id"])->update([
            "identification_type" => $request["identification_type"],
            "identification_number" => $request["identification_number"],
            "name" => $request["name"],
            "last_name" => $request["last_name"],
            "identification_phone" => $request["identification_phone"],
            "license_plate" => $request["license_plate"],
            "type" => $request["type"]
        ]);

        if($data){
            return response()->json([
                "message" => "Se ha actualizado el registro de forma existosa"
            ], 200);
        }else{
            return response()->json([
                "message" => "Se ha presentado un error."
            ], 400);
        }
    }
    public function disabled(Request $request)
    {
        $data = Driver::where("id", $request["id"])->update([
            "status" => $request["status"]
        ]);

        return response()->json($data, 200);
    }
}
