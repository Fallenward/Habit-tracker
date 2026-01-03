<?php

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\User;

test('user can get statistics', function () {
    $user = User::factory()->create();
    $habits = Habit::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/stats');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'range',
            'start_date',
            'end_date',
            'current_streak',
            'completion' => ['completed', 'total', 'rate'],
            'weekly_overview',
            'top_habits',
        ]);
});

test('user can get statistics with custom range', function () {
    $user = User::factory()->create();
    Habit::factory()->count(2)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/stats?range=7d');

    $response->assertStatus(200)
        ->assertJson([
            'range' => '7d',
        ]);
});

test('stats show correct completion rate', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);

    // Create logs for last 5 days
    for ($i = 0; $i < 5; $i++) {
        HabitLog::factory()->create([
            'habit_id' => $habit->id,
            'log_date' => now()->subDays($i)->format('Y-m-d'),
            'completed' => true,
        ]);
    }

    $response = $this->actingAs($user)->getJson('/api/v1/stats?range=10d');

    $response->assertStatus(200);

    $completion = $response->json('completion');
    expect($completion['completed'])->toBe(5)
        ->and($completion['total'])->toBe(10); // 1 habit × 10 days
});

test('stats calculate current streak correctly', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);

    // Create logs for last 5 consecutive days
    for ($i = 0; $i < 5; $i++) {
        HabitLog::factory()->create([
            'habit_id' => $habit->id,
            'log_date' => now()->subDays($i)->format('Y-m-d'),
            'completed' => true,
        ]);
    }

    $response = $this->actingAs($user)->getJson('/api/v1/stats?range=30d');

    $response->assertStatus(200);

    $streak = $response->json('current_streak');
    expect($streak)->toBeGreaterThanOrEqual(5);
});

test('stats show weekly overview', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/stats?range=30d');

    $response->assertStatus(200);

    $weeklyOverview = $response->json('weekly_overview');
    expect($weeklyOverview)->toBeArray()
        ->and(count($weeklyOverview))->toBe(7);
});

test('stats show top habits', function () {
    $user = User::factory()->create();
    $habit1 = Habit::factory()->create(['user_id' => $user->id]);
    $habit2 = Habit::factory()->create(['user_id' => $user->id]);

    // Habit 1: 8 completions out of 10 days
    for ($i = 0; $i < 8; $i++) {
        HabitLog::factory()->create([
            'habit_id' => $habit1->id,
            'log_date' => now()->subDays($i)->format('Y-m-d'),
            'completed' => true,
        ]);
    }

    // Habit 2: 3 completions out of 10 days
    for ($i = 0; $i < 3; $i++) {
        HabitLog::factory()->create([
            'habit_id' => $habit2->id,
            'log_date' => now()->subDays($i)->format('Y-m-d'),
            'completed' => true,
        ]);
    }

    $response = $this->actingAs($user)->getJson('/api/v1/stats?range=10d');

    $response->assertStatus(200);

    $topHabits = $response->json('top_habits');
    expect($topHabits)->toBeArray()
        ->and($topHabits[0]['id'])->toBe($habit1->id) // Habit 1 should be first (higher completion rate)
        ->and($topHabits[0]['completion_rate'])->toBeGreaterThan($topHabits[1]['completion_rate']);
});

test('stats only include user own habits', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $userHabit = Habit::factory()->create(['user_id' => $user->id]);
    $otherHabit = Habit::factory()->create(['user_id' => $otherUser->id]);

    HabitLog::factory()->create([
        'habit_id' => $userHabit->id,
        'log_date' => now()->format('Y-m-d'),
        'completed' => true,
    ]);

    HabitLog::factory()->create([
        'habit_id' => $otherHabit->id,
        'log_date' => now()->format('Y-m-d'),
        'completed' => true,
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/stats?range=30d');

    $response->assertStatus(200);

    $completion = $response->json('completion');
    expect($completion['completed'])->toBe(1); // Only user's habit
});

test('stats handle zero habits gracefully', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/stats?range=30d');

    $response->assertStatus(200)
        ->assertJson([
            'current_streak' => 0,
            'completion' => [
                'completed' => 0,
                'total' => 0,
                'rate' => 0,
            ],
        ]);
});

