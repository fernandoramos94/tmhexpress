<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{
    public function register(Request $request)
    {
        $user = User::create([
            'name' =>  $request['name'],
            'email' => $request['email'],
            'type_user' => 3,
            'password' => Hash::make($request['password'])
        ]);

        $user = Account::create([
            'name' => $request["name"],
            'last_name' => $request["last_name"],
            'phone' => $request["phone"],
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => "Se ha creado su usuario de forma exitosa."
        ], 200);
    }

    public function make_seed()
    {
        return date('GHs');
    }

    public function generateCode(Request $request)
    {

        $code = $this->make_seed();

        $data = [
            "code" => $code,
            "name" => $request["name"] . " ". $request["last_name"]
        ];

        $email = $request["email"];
        $client = $request["name"] . " ". $request["last_name"];

        
        Mail::send("mail.codeGenerate", ["data" => (object)$data], function ($message) use ($email, $client) {
            $message->to($email, $client)
                ->subject("Codigo de verificacion");
            $message->from("no-reply@tmhexpress.com", "Tmh Express");
        });

        return response()->json([
            "code" => $code
        ], 200);
    }

    public function send_password(Request $request)
    {
        $data_code = Hash::make(date('Y-m-d-H:-m-s'));

        $data = [
            "code" => $data_code
        ];

        $email = $request["email"];

        Mail::send("mail.recoveryPassword", ["data" => (object)$data], function ($message) use ($email) {
            $message->to($email)
                ->subject("Recuperar contraseña");
            $message->from("no-reply@tmhexpress.com", "Tmh Express");
        });

        User::where("email", $email)->update([
            "remember_token" => $data_code
        ]);

        return response()->json([
            "message" => "Hemos enviado a tu correo un link para recuperar tu contraseña"
        ], 200);
    }
    public function recovery_password(Request $request)
    {
        User::where("remember_token", $request["code"])->update([
            "password" => Hash::make($request["password"])
        ]);

        return response()->json(["message" => "Su contraseña ha sido actualizada de forma exirosa."], 200);
    } 
}
