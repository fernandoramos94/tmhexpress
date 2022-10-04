<?php

namespace App\Http\Controllers;

use App\Models\CancellationReason;
use Illuminate\Http\Request;

class CancellationReasonController extends Controller
{
    public function getReason()
    {
        $data = CancellationReason::get();

        return response()->json($data, 200);
    } 
}
