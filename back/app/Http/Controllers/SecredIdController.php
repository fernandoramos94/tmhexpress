<?php

namespace App\Http\Controllers;

use App\Models\SecredId;
use Illuminate\Http\Request;

class SecredIdController extends Controller
{
    public function addSecred(Request $request)
    {
        $data = SecredId::create([
            'token' => $request["token"],
            'user_id' => $request->user()->id
        ]);

        if($data){
            return response()->json([
                "message" => "Clave generada con exito"
            ], 200);
        }else{
            return response()->json([
                "message" => "Se ha presentado un problema"
            ], 401);
        }
    }
}
