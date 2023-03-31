<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Images extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'image_path',
        'user_id',
        'is_avatar'
    ];
}
