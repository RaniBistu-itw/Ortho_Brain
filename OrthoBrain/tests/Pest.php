<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

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
