<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportRecord extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'line_count',
        'first_reading_at',
        'last_reading_at',
        'status',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'first_reading_at' => 'immutable_datetime',
            'last_reading_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
