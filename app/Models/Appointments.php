<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    use HasFactory;

    protected $fillable = ['telephone', 'client_firstname', 'client_lastname', 'doctor_firstname', 'doctor_lastname', 'appointment_time'];
}
