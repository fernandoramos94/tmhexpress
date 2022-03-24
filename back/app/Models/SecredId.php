<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecredId extends Model
{
    use HasFactory;

    protected $table = "secred_id";

    protected $guarded = [];
}
