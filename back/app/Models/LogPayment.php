<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPayment extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = "log_payment";
}
