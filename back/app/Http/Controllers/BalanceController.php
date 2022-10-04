<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function getData(Request $request)
    {
        $balance = Balance::where("user_id",$request->user()->id)->sum("balance");
        return response()->json($balance, 200);
    } 
}
