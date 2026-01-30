<?php

declare(strict_types=1);

use App\Livewire\Settings\DeleteUserForm;
use App\Models\User;
use Livewire\Livewire;

test('account can be deleted', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DeleteUserForm::class)
        ->set('password', 'password')
        ->call('deleteUser');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('account deletion requires correct password', function (): void {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DeleteUserForm::class)
        ->set('password', 'wrong-password')
        ->call('deleteUser')
        ->assertHasErrors('password');

    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
