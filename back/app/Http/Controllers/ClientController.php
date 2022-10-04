<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function getClients($status)
    {
        $data = Client::select("clients.*", "users.status")->join("users", "users.id", "=", "clients.user_id")->where("users.status", (int)$status)->get();

        return response()->json($data, 200);
    }

    public function getBy($id)
    {
        $data = Client::select("clients.*", "users.email")->join("users", "users.id", "=", "clients.user_id")->where("clients.id", $id)->first();

        return response()->json($data, 200);
    }

    public function edit(Request $request)
    {
    

        User::where("id", $request["user_id"])->update([
            'name' =>  $request['name'],
            'email' => $request['email'],
            
        ]);

        if(isset($request["password"])){
            User::where("id", $request["user_id"])->update([
                'password' =>  Hash::make($request['password'])
            ]);
        }


        $client = Client::where("id", $request["id"])->update([
            'name' => $request["name"],
            'last_name' => $request["last_name"],
            'address' => $request["address"],
            'identification_type' => $request["identification_type"],
            'identification_number' => $request["identification_number"],
            'phone' => $request["phone"],
            'cx' => $request["cx"],
            'cy' => $request["cy"]
        ]);

        return response()->json($client, 200);
    }

    public function delete($id)
    {
        
    }
}
