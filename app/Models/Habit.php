<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = ['id'];


    protected $casts = [
        'schedule' => 'array',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class, 'habit_id', 'id');
    }
}
