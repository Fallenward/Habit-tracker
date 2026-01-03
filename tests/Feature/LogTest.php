<?php

use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\User;

test('user can mark habit as done', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);
    $date = now()->format('Y-m-d');

    $response = $this->actingAs($user)->putJson("/api/v1/logs/{$date}", [
        'habit_id' => $habit->id,
        'completed' => true,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'log' => ['id', 'habit_id', 'date', 'completed'],
        ])
        ->assertJson([
            'log' => [
                'habit_id' => $habit->id,
                'date' => $date,
                'completed' => true,
            ],
        ]);

    $this->assertDatabaseHas('habit_logs', [
        'habit_id' => $habit->id,
        'completed' => 1,
    ]);
});

test('user can mark habit as undone', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);
    $date = now()->format('Y-m-d');

    // First mark as done
    HabitLog::factory()->create([
        'habit_id' => $habit->id,
        'log_date' => $date,
        'completed' => true,
    ]);

    // Then mark as undone
    $response = $this->actingAs($user)->putJson("/api/v1/logs/{$date}", [
        'habit_id' => $habit->id,
        'completed' => false,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'log' => [
                'completed' => false,
            ],
        ]);

    $this->assertDatabaseHas('habit_logs', [
        'habit_id' => $habit->id,
        'completed' => 0,
    ]);
});

test('user can toggle habit completion', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);
    $date = now()->format('Y-m-d');

    // First mark as done
    $response1 = $this->actingAs($user)->putJson("/api/v1/logs/{$date}", [
        'habit_id' => $habit->id,
        'completed' => true,
    ]);

    $response1->assertStatus(200);

    // Then toggle to undone
    $response2 = $this->actingAs($user)->putJson("/api/v1/logs/{$date}", [
        'habit_id' => $habit->id,
        'completed' => false,
    ]);

    $response2->assertStatus(200);

    // Should only have one log entry (updateOrCreate)
    $this->assertDatabaseCount('habit_logs', 1);
});

test('user cannot mark other users habit as done', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $otherUser->id]);
    $date = now()->format('Y-m-d');

    $response = $this->actingAs($user)->putJson("/api/v1/logs/{$date}", [
        'habit_id' => $habit->id,
        'completed' => true,
    ]);

    $response->assertStatus(403);
});

test('validation fails when habit_id is missing', function () {
    $user = User::factory()->create();
    $date = now()->format('Y-m-d');

    $response = $this->actingAs($user)->putJson("/api/v1/logs/{$date}", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['habit_id']);
});

test('validation fails when habit_id does not exist', function () {
    $user = User::factory()->create();
    $date = now()->format('Y-m-d');

    $response = $this->actingAs($user)->putJson("/api/v1/logs/{$date}", [
        'habit_id' => 'non-existent-id',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['habit_id']);
});

test('validation fails when date format is invalid', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->putJson('/api/v1/logs/invalid-date', [
        'habit_id' => $habit->id,
    ]);

    $response->assertStatus(422);
});

