<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = [
        'customer_id',
        'available_id',
        'appoint_name',
        'appoint_email',
        'appoint_status',
        'reminder_sent',
    ];

    public function customer(){
        return $this->belongsTo('App\Models\User', 'customer_id');
    }

    public function availability(){
        return $this->belongsTo('App\Models\Availability', 'available_id');
    }
    
}
