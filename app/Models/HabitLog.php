<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitLog extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'habit_id',
        'log_date',
        'completed',
    ];

    protected $casts = [
        'log_date' => 'date',
        'completed' => 'boolean',
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class, 'habit_id', 'id');
    }
}
