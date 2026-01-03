<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Get statistics for habits
     * GET /v1/stats?range=30d
     */
    public function index(Request $request)
    {
        $request->validate([
            'range' => ['sometimes', 'string', 'regex:/^\d+d$/'], // e.g., 30d, 7d
        ]);

        $user = $request->user();
        $range = $request->input('range', '30d');

        // Parse range (e.g., "30d" -> 30 days)
        preg_match('/(\d+)d/', $range, $matches);
        $days = isset($matches[1]) ? (int)$matches[1] : 30;

        $endDate = now();
        $startDate = now()->subDays($days);

        // Get all habits for the user
        $habits = Habit::where('user_id', $user->id)->get();

        // Get all logs in the range (only completed ones)
        $logs = HabitLog::whereIn('habit_id', $habits->pluck('id'))
            ->whereDate('log_date', '>=', $startDate->format('Y-m-d'))
            ->whereDate('log_date', '<=', $endDate->format('Y-m-d'))
            ->where('completed', true)
            ->get();

        // Calculate current streak
        $currentStreak = $this->calculateStreak($habits, $logs, $endDate);

        // Calculate completion rate
        $totalPossible = $habits->count() * $days;
        $totalCompleted = $logs->count();
        $completionRate = $totalPossible > 0 ? round(($totalCompleted / $totalPossible) * 100, 1) : 0;

        // Weekly overview (last 7 days)
        $weeklyOverview = $this->getWeeklyOverview($habits, $logs, $endDate);

        // Top habits (by completion rate)
        $topHabits = $this->getTopHabits($habits, $logs, $startDate, $endDate);

        return response()->json([
            'range' => $range,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'current_streak' => $currentStreak,
            'completion' => [
                'completed' => $totalCompleted,
                'total' => $totalPossible,
                'rate' => $completionRate,
            ],
            'weekly_overview' => $weeklyOverview,
            'top_habits' => $topHabits,
        ]);
    }

    /**
     * Calculate current streak (consecutive days with at least one completed habit)
     */
    private function calculateStreak($habits, $logs, $endDate): int
    {
        if ($habits->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $currentDate = $endDate->copy();

        while (true) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayLogs = $logs->filter(function ($log) use ($dateKey) {
                return $log->log_date->format('Y-m-d') === $dateKey;
            });

            if ($dayLogs->isEmpty()) {
                break;
            }

            $streak++;
            $currentDate->subDay();

            // Limit to prevent infinite loop
            if ($streak > 365) {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get weekly overview (last 7 days)
     */
    private function getWeeklyOverview($habits, $logs, $endDate): array
    {
        $overview = [];
        $weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        for ($i = 6; $i >= 0; $i--) {
            $date = $endDate->copy()->subDays($i);
            $dateKey = $date->format('Y-m-d');
            $dayLogs = $logs->where('log_date', $dateKey);

            $overview[] = [
                'day' => $weekdays[$date->dayOfWeek],
                'date' => $dateKey,
                'completed_count' => $dayLogs->count(),
                'total_habits' => $habits->count(),
            ];
        }

        return $overview;
    }

    /**
     * Get top habits by completion rate
     */
    private function getTopHabits($habits, $logs, $startDate, $endDate): array
    {
        $days = $startDate->diffInDays($endDate) + 1;
        $habitStats = [];

        foreach ($habits as $habit) {
            $habitLogs = $logs->where('habit_id', $habit->id);
            $completedCount = $habitLogs->count();
            $completionRate = $days > 0 ? round(($completedCount / $days) * 100, 1) : 0;

            $habitStats[] = [
                'id' => $habit->id,
                'title' => $habit->title,
                'completion_rate' => $completionRate,
                'completed_count' => $completedCount,
                'total_days' => $days,
            ];
        }

        usort($habitStats, function ($a, $b) {
            return $b['completion_rate'] <=> $a['completion_rate'];
        });

        return array_slice($habitStats, 0, 5); // Top 5
    }
}
