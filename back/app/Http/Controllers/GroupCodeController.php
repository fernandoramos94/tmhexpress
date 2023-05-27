<?php

namespace App\Http\Controllers;

use App\Models\GroupCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupCodeController extends Controller
{

    public function list()
    {
        $data = GroupCode::get();

        foreach ($data as $item) {
            $item->codes = json_decode($item->codes);
        }

        return response()->json($data, 200);
    }
    public function add(Request $request)
    {
        GroupCode::insert([
            "name" => $request["name"],
            "codes" => json_encode($request["code"])
        ]);

        return response()->json(["ok" => true, "msg" => "Proceso realizado con exito"], 200);
    }
    public function update(Request $request)
    {
        GroupCode::where("id", $request["id"])->update([
            "name" => $request["name"],
            "codes" => json_encode($request["code"])
        ]);

        return response()->json(["ok" => true, "msg" => "Proceso realizado con exito"], 200);
    }

    public function delete($id)
    {
        GroupCode::where("id", $id)->delete();

        return response()->json(["ok" => true, "msg" => "Proceso realizado con exito"], 200);
    }

    public function getCodes()
    {
        $data = DB::select("select code as id, code as itemName from zip_code 
        group by code
        order by code asc");

        return response()->json($data, 200);
    }
}
