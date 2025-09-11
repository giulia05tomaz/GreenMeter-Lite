<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $fillable = [
        'ts',
        'metric',
        'value',
        'unit',
        'source',
    ];

    public $timestamps = true;

    protected $dates = ['ts'];
}