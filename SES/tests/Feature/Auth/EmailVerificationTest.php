<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

test('active users can access the application without verified email', function () {
    $user = User::factory()->create(['email_verified_at' => null]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertOk();
});

test('email verification routes are not registered', function () {
    expect(Route::has('verification.notice'))->toBeFalse()
        ->and(Route::has('verification.send'))->toBeFalse()
        ->and(Route::has('verification.verify'))->toBeFalse();
});
