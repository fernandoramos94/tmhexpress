<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function getClients(Request $request)
    {
        $data = Client::select("clients.*", "users.status")->join("users", "users.id", "=", "clients.user_id")->get();

        return response()->json($data, 200);
    }
}
