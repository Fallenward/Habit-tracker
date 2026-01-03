<?php

use App\Models\Habit;
use App\Models\User;

test('user can list their habits', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $userHabits = Habit::factory()->count(3)->create(['user_id' => $user->id]);
    Habit::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/habits');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description', 'schedule'],
            ],
        ]);
});

test('user cannot see other users habits', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Habit::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/habits');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('user can create a habit', function () {
    $user = User::factory()->create();

    $habitData = [
        'title' => 'Drink water',
        'description' => 'Drink 8 cups of water daily',
        'schedule' => [
            'rrule' => 'FREQ=DAILY',
            'time' => '08:00',
        ],
    ];

    $response = $this->actingAs($user)->postJson('/api/v1/habits', $habitData);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'description', 'schedule'],
        ])
        ->assertJson([
            'data' => [
                'name' => 'Drink water',
                'description' => 'Drink 8 cups of water daily',
            ],
        ]);

    $this->assertDatabaseHas('habits', [
        'user_id' => $user->id,
        'title' => 'Drink water',
        'description' => 'Drink 8 cups of water daily',
    ]);
});

test('user can view a specific habit', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/habits/{$habit->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => ['id', 'name', 'description', 'schedule'],
        ])
        ->assertJson([
            'data' => [
                'id' => $habit->id,
            ],
        ]);
});

test('user cannot view other users habit', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->getJson("/api/v1/habits/{$habit->id}");

    $response->assertStatus(403);
});

test('user can update their habit', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);

    $updateData = [
        'title' => 'Updated title',
        'description' => 'Updated description',
    ];

    $response = $this->actingAs($user)->putJson("/api/v1/habits/{$habit->id}", $updateData);

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'name' => 'Updated title',
                'description' => 'Updated description',
            ],
        ]);

    $this->assertDatabaseHas('habits', [
        'id' => $habit->id,
        'title' => 'Updated title',
        'description' => 'Updated description',
    ]);
});

test('user cannot update other users habit', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->putJson("/api/v1/habits/{$habit->id}", [
        'title' => 'Hacked title',
    ]);

    $response->assertStatus(403);
});

test('user can delete their habit', function () {
    $user = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/habits/{$habit->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('habits', [
        'id' => $habit->id,
    ]);
});

test('user cannot delete other users habit', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $habit = Habit::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/habits/{$habit->id}");

    $response->assertStatus(403);
});

test('user can get today checklist', function () {
    $user = User::factory()->create();
    $habits = Habit::factory()->count(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/habits/today');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'date',
            'total_habits',
            'completed_count',
            'habits' => [
                '*' => ['id', 'title', 'description', 'schedule', 'completed'],
            ],
        ])
        ->assertJson([
            'total_habits' => 3,
            'completed_count' => 0,
        ]);
});

test('validation fails when creating habit without required fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/habits', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'description', 'schedule']);
});

test('validation fails when schedule is invalid', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/habits', [
        'title' => 'Test',
        'description' => 'Test description',
        'schedule' => [
            'rrule' => 'INVALID',
            'time' => 'invalid-time',
        ],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['schedule.time']);
});

