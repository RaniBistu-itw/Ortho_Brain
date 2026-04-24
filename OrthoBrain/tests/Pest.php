<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Unit tests get the Laravel TestCase (so facades + config() work) but NOT
// RefreshDatabase — nothing in Unit/ should need the database.
pest()->extend(TestCase::class)
    ->in('Unit');

function loginAsAdmin(): User
{
    $user = User::factory()->admin()->create();
    test()->actingAs($user);
    return $user;
}

function loginAsDoctor(): User
{
    $user = User::factory()->create(['role' => 'DOCTOR', 'is_active' => true]);
    test()->actingAs($user);
    return $user;
}
