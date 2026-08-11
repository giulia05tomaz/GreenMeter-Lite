<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmissionFactor extends Model
{
    protected $fillable = ['metric', 'factor', 'unit_in', 'unit_out'];

    protected function casts(): array
    {
        return ['factor' => 'decimal:8'];
    }
}
