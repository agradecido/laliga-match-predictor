<?php

use App\Models\User;

it('forbids non-admins from every admin users route', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $other = User::factory()->create();

    $this->actingAs($user)->get('/admin/users')->assertForbidden();
    $this->actingAs($user)->get('/admin/users/create')->assertForbidden();
    $this->actingAs($user)->post('/admin/users', [
        'name' => 'Nuevo',
        'email' => 'nuevo@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertForbidden();
    $this->actingAs($user)->get("/admin/users/{$other->id}")->assertForbidden();
    $this->actingAs($user)->get("/admin/users/{$other->id}/edit")->assertForbidden();
    $this->actingAs($user)->put("/admin/users/{$other->id}", [
        'name' => $other->name,
        'email' => $other->email,
    ])->assertForbidden();
    $this->actingAs($user)->delete("/admin/users/{$other->id}")->assertForbidden();
});

it('lets an admin create, update and delete a player', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Jugador Nuevo',
        'email' => 'jugador@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'is_admin' => false,
    ]);
    $response->assertRedirect();

    $created = User::where('email', 'jugador@example.com')->firstOrFail();
    expect($created->is_admin)->toBeFalse();

    $this->actingAs($admin)->put("/admin/users/{$created->id}", [
        'name' => 'Jugador Editado',
        'email' => 'jugador@example.com',
        'is_admin' => true,
    ])->assertRedirect();

    expect($created->refresh()->name)->toBe('Jugador Editado')
        ->and($created->is_admin)->toBeTrue();

    $this->actingAs($admin)->delete("/admin/users/{$created->id}")->assertRedirect();
    expect(User::find($created->id))->toBeNull();
});

it('prevents an admin from deleting themselves', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->delete("/admin/users/{$admin->id}")->assertForbidden();
    expect(User::find($admin->id))->not->toBeNull();
});

it('prevents an admin from removing their own admin flag', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
        'name' => $admin->name,
        'email' => $admin->email,
        'is_admin' => false,
    ])->assertRedirect();

    expect($admin->refresh()->is_admin)->toBeTrue();
});

it('requires a unique email when creating a player', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $existing = User::factory()->create();

    $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Duplicado',
        'email' => $existing->email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');
});
