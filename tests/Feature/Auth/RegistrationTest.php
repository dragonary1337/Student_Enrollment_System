<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'username' => 'john-doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('login', absolute: false));

    $this->assertGuest();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'username' => 'john-doe',
        'role' => 'Operator',
        'status' => 'Pending',
    ]);
});
