<?php

namespace App\Http\Controllers;

use App\Imports\ZipCodeImport;
use App\Models\ZipCode;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ZipCodeController extends Controller
{
    public function import(Request $request)
    {
        $file = $request->file("file");
        $resp = Excel::import(new ZipCodeImport, $file);

        if($resp){
            return response()->json(["message" => "se han importado los codigo postales de forma exitosa. "], 200);
        }else{
            return response()->json(["message" => "se ha presentado un error al importar los codigo postales. "], 400);
        }
    }

    public function getAll()
    {
        $data = ZipCode::all();

        return response()->json($data,200);
    }

    public function getId($code)
    {
        $data = ZipCode::where("code", $code)->count();
        return $data;
    }

    public function getByCode($code)
    {
        $code = ''.$code;
        $data = ZipCode::where("code", "like", "%". intval($code)."%")->get();

        return response()->json($data, 200);
    }

    public function getByCodeApi($code)
    {
        $code = ''.$code;
        $data = ZipCode::where("code", "like", "%". intval($code)."%")->get();

        if(count($data)){
            return response()->json([
                "msg" => "",
                "data" => $data
            ], 200);
        }else{
            return response()->json([
                "msg" => "Codigo postal no encontrado",
                "data" => []
            ], 200);
        }
    }

    public function delete($id)
    {
        ZipCode::where("id", $id)->delete();
        return response()->json(["message" => " Codigo postal eliminado de forma exitosa."], 200);
    } 
}
