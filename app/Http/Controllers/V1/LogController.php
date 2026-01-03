<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    /**
     * Toggle habit completion for a specific date
     * PUT /v1/logs/{date}
     */
    public function update(Request $request, string $date)
    {
        $request->validate([
            'habit_id' => ['required', 'string', 'exists:habits,id'],
            'completed' => ['sometimes', 'boolean'],
        ]);

        $habit = Habit::findOrFail($request->habit_id);

        // Check if the habit belongs to the authenticated user
        if ($habit->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        // Validate date format
        try {
            $logDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }

        $completed = $request->boolean('completed', true);

        // Use updateOrCreate to toggle or create log
        $log = HabitLog::where('habit_id', $habit->id)
            ->whereDate('log_date', $logDate)
            ->first();

        if ($log) {
            $log->update(['completed' => $completed]);
        } else {
            $log = HabitLog::create([
                'habit_id' => $habit->id,
                'log_date' => $logDate,
                'completed' => $completed,
            ]);
        }

        return response()->json([
            'message' => $completed ? 'Habit marked as done' : 'Habit marked as undone',
            'log' => [
                'id' => $log->id,
                'habit_id' => $log->habit_id,
                'date' => $log->log_date->format('Y-m-d'),
                'completed' => $log->completed,
            ],
        ]);
    }
}
