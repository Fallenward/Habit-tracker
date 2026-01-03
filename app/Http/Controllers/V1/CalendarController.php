<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    /**
     * Get calendar view for a specific month
     * GET /v1/calendar?month=YYYY-MM
     */
    public function index(Request $request)
    {
        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $user = $request->user();
        $month = $request->input('month');

        try {
            $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid month format. Use YYYY-MM'], 422);
        }

        // Get all habits for the user
        $habits = Habit::where('user_id', $user->id)->get();

        // Get all logs for the month
        $logs = HabitLog::whereIn('habit_id', $habits->pluck('id'))
            ->whereBetween('log_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('completed', true)
            ->get()
            ->groupBy(function ($log) {
                return $log->log_date->format('Y-m-d');
            });

        // Build calendar data
        $calendar = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayLogs = $logs->get($dateKey, collect());

            $calendar[] = [
                'date' => $dateKey,
                'day' => $currentDate->day,
                'weekday' => $currentDate->format('D'),
                'completed_habits' => $dayLogs->pluck('habit_id')->unique()->count(),
                'total_habits' => $habits->count(),
                'habits' => $dayLogs->map(function ($log) {
                    return [
                        'habit_id' => $log->habit_id,
                        'completed' => $log->completed,
                    ];
                })->values(),
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'month' => $month,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'calendar' => $calendar,
        ]);
    }

    /**
     * Get details for a specific date
     * GET /v1/calendar/{date}
     */
    public function show(Request $request, string $date)
    {
        try {
            $logDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format. Use YYYY-MM-DD'], 422);
        }

        $user = $request->user();

        // Get all habits for the user
        $habits = Habit::where('user_id', $user->id)->get();

        // Get logs for the specific date
        $logs = HabitLog::whereIn('habit_id', $habits->pluck('id'))
            ->whereDate('log_date', $logDate)
            ->get()
            ->keyBy('habit_id');

        // Build response with all habits and their completion status
        $habitsData = $habits->map(function ($habit) use ($logs) {
            $log = $logs->get($habit->id);
            return [
                'id' => $habit->id,
                'title' => $habit->title,
                'description' => $habit->description,
                'completed' => $log ? $log->completed : false,
                'log_id' => $log ? $log->id : null,
            ];
        });

        return response()->json([
            'date' => $logDate,
            'total_habits' => $habits->count(),
            'completed_count' => $logs->where('completed', true)->count(),
            'habits' => $habitsData,
        ]);
    }
}
