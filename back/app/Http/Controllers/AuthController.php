<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string',
            'identification_type' => 'required',
            'identification_number' => 'required',
            'phone' => 'required'
        ]);



        $user = User::create([
            'name' =>  $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password'])
        ]);

        $client = Client::create([
            'name' => $request["name"],
            'last_name' => $request["last_name"],
            'address' => $request["address"],
            'identification_type' => $request["identification_type"],
            'identification_number' => $request["identification_number"],
            'phone' => $request["phone"],
            'cx' => $request["cx"],
            'cy' => $request["cy"],
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {

        if(!Auth::attempt(['email' => $request["email"], 'password' => $request["password"], 'status'  => 1])){
            return response()->json([
                'message' => 'Email or password invalid'
            ], 401);
        }

        $user = User::where("email", $request["email"])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user,
        ]);
    }

    public function getInfo(Request $request)
    {
        return $request->user();
    }
    public function disabledUser($id, $status)
    {
        User::where("id", $id)->update(["status" => $status]);
        DB::table("personal_access_tokens")->where("tokenable_id", $id)->delete();
        return response()->json(["message" => "El usuario se ha desactivado de forma exitosa."], 200);
    }
}
