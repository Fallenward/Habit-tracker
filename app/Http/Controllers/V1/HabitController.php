<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HabitsResource;
use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $habits = Habit::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return HabitsResource::collection($habits);
    }

    /**
     * Get today's checklist with completion status
     * GET /v1/habits/today
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');

        // Get all habits for the user
        $habits = Habit::where('user_id', $user->id)->get();

        // Get today's logs
        $logs = HabitLog::whereIn('habit_id', $habits->pluck('id'))
            ->where('log_date', $today)
            ->get()
            ->keyBy('habit_id');

        // Build response with completion status
        $todayHabits = $habits->map(function ($habit) use ($logs, $today) {
            $log = $logs->get($habit->id);
            return [
                'id' => $habit->id,
                'title' => $habit->title,
                'description' => $habit->description,
                'schedule' => $habit->schedule,
                'completed' => $log ? $log->completed : false,
                'log_id' => $log ? $log->id : null,
            ];
        });

        return response()->json([
            'date' => $today,
            'total_habits' => $habits->count(),
            'completed_count' => $logs->where('completed', true)->count(),
            'habits' => $todayHabits,
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['required','string'],
            'schedule' => ['required','array'],
            'schedule.rrule' => ['required','string'],
            'schedule.time' => ['required','date_format:H:i'],
        ]);

        // Add user_id to the validated data
        $data['user_id'] = $request->user()->id;

        $habit = Habit::create($data);

        return new HabitsResource($habit);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Habit $habit)
    {
        // Check if the habit belongs to the authenticated user
        if ($habit->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        return new HabitsResource($habit);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'title' => ['sometimes','required','string','max:255'],
            'description' => ['sometimes','required','string'],
            'schedule' => ['sometimes','required','array'],
            'schedule.rrule' => ['required_with:schedule','string'],
            'schedule.time' => ['required_with:schedule','date_format:H:i'],
        ]);

        $habit->update($data);

        return new HabitsResource($habit);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $habit->delete();

        return response()->noContent();
    }
}
