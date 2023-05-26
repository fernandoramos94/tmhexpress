<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCode extends Model
{
    use HasFactory;
    protected $guarded = [];

    public $timestamps = false;

    protected $table = "group_code";
}
