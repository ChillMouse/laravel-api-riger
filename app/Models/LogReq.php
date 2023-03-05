<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogReq extends Model
{
    use HasFactory;
    protected $table = 'logging_requests';
}
