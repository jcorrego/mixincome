<?php

declare(strict_types=1);

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

test('password can be updated via action', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $action = new UpdateUserPassword();
    $action->update($user, [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('wrong current password fails via action', function (): void {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create();
    $action = new UpdateUserPassword();

    try {
        $action->update($user, [
            'current_password' => 'wrong',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
        $this->fail('Expected ValidationException');
    } catch (ValidationException $e) {
        expect($e->errorBag)->toBe('updatePassword');
    }
});
