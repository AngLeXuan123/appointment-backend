<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [

        'doctor_id',
        'availableDate',
        'startTime',
        'endTime',
    ];

    public function doctor(){
        return $this->belongsTo('App\Models\User', 'doctor_id');
    }

}
