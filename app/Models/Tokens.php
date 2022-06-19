<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tokens extends Model
{
    use HasFactory;

    protected $connection = 'mysql_etmobile';
    protected $table = 'tokens';

    public function getUser() {
        return $this->belongsTo(MobileUser::class, 'id', 'id_user');
    }
}
