<?php

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\User;
use Carbon\Carbon;

test('user can get calendar for a month', function () {
    $user = User::factory()->create();
    $habits = Habit::factory()->count(3)->create(['user_id' => $user->id]);
    $month = now()->format('Y-m');

    $response = $this->actingAs($user)->getJson("/api/v1/calendar?month={$month}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'month',
            'start_date',
            'end_date',
            'calendar' => [
                '*' => ['date', 'day', 'weekday', 'completed_habits', 'total_habits', 'habits'],
            ],
        ]);
});

test('user can see completed habits in calendar', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);
    $date = now()->format('Y-m-d');
    $month = now()->format('Y-m');

    HabitLog::factory()->create([
        'habit_id' => $habit->id,
        'log_date' => $date,
        'completed' => true,
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/calendar?month={$month}");

    $response->assertStatus(200);

    $calendarData = $response->json('calendar');
    $todayData = collect($calendarData)->firstWhere('date', $date);

    expect($todayData)->not->toBeNull()
        ->and($todayData['completed_habits'])->toBe(1)
        ->and($todayData['total_habits'])->toBe(1);
});

test('user can get details for a specific date', function () {
    $user = User::factory()->create();
    $habits = Habit::factory()->count(3)->create(['user_id' => $user->id]);
    $date = now()->format('Y-m-d');

    // Mark one habit as done
    HabitLog::factory()->create([
        'habit_id' => $habits->first()->id,
        'log_date' => $date,
        'completed' => true,
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/calendar/{$date}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'date',
            'total_habits',
            'completed_count',
            'habits' => [
                '*' => ['id', 'title', 'description', 'completed', 'log_id'],
            ],
        ])
        ->assertJson([
            'date' => $date,
            'total_habits' => 3,
            'completed_count' => 1,
        ]);
});

test('validation fails when month format is invalid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/calendar?month=invalid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['month']);
});

test('validation fails when date format is invalid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/calendar/invalid-date');

    $response->assertStatus(422);
});

test('calendar shows correct number of days in month', function () {
    $user = User::factory()->create();
    $month = '2025-02'; // February 2025 has 28 days

    $response = $this->actingAs($user)->getJson("/api/v1/calendar?month={$month}");

    $response->assertStatus(200);

    $calendar = $response->json('calendar');
    expect(count($calendar))->toBe(28);
});

test('user only sees their own habits in calendar', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $userHabit = Habit::factory()->create(['user_id' => $user->id]);
    $otherHabit = Habit::factory()->create(['user_id' => $otherUser->id]);

    $date = now()->format('Y-m-d');
    $month = now()->format('Y-m');

    HabitLog::factory()->create([
        'habit_id' => $userHabit->id,
        'log_date' => $date,
        'completed' => true,
    ]);

    HabitLog::factory()->create([
        'habit_id' => $otherHabit->id,
        'log_date' => $date,
        'completed' => true,
    ]);

    $response = $this->actingAs($user)->getJson("/api/v1/calendar/{$date}");

    $response->assertStatus(200)
        ->assertJson([
            'total_habits' => 1,
            'completed_count' => 1,
        ]);
});

