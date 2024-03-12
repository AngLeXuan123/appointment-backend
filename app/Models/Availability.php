<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = [

        'doctor_id',
        'availableDate',
        'startTime',
        'endTime',
    ];

    public function doctor()
    {
        return $this->belongsTo('App\Models\User', 'doctor_id');
    }

    public function appointment(){
        return $this->hasOne('App\Models\Appointment', 'available_id');
    }
}
