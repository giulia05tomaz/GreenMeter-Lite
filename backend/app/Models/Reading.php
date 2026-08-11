<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reading extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'ts', 'metric', 'value', 'unit', 'source'];

    protected function casts(): array
    {
        return [
            'ts' => 'immutable_datetime',
            'value' => 'decimal:3',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
