<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileUser extends Model
{
    use HasFactory;

    protected $connection = 'mysql_etmobile';
    protected $table = 'mobile_users';
}
