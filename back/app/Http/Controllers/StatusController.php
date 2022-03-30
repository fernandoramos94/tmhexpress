<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function getStatus(Request $request)
    {
        $data = Status::whereNotIn("id", [1,2])->get();

        return response()->json($data, 200);
    }
}
