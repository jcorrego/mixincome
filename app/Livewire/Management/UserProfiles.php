<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Http\Requests\StoreUserProfileRequest;
use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\Address;
use App\Models\Jurisdiction;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class UserProfiles extends Component
{
    public string $tax_id = '';

    public string $jurisdiction_id = '';

    public string $address_id = '';

    public ?int $editingId = null;

    public function create(): void
    {
        $storeRequest = new StoreUserProfileRequest();

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($storeRequest->rules(), $storeRequest->messages());

        /** @var User $user */
        $user = auth()->user();

        if ($this->address_id !== '') {
            $address = Address::query()->findOrFail($this->address_id);
            $this->authorize('view', $address);
            $validated['address_id'] = (int) $this->address_id;
        } else {
            $validated['address_id'] = null;
        }

        $user->userProfiles()->create($validated);

        $this->dispatch('modal-close', name: 'create-profile');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $profile = UserProfile::query()->findOrFail($id);

        $this->authorize('update', $profile);

        $this->editingId = $profile->id;
        $this->jurisdiction_id = (string) $profile->jurisdiction_id;
        $this->tax_id = $profile->tax_id;
        $this->address_id = (string) ($profile->address_id ?? '');

        $this->dispatch('modal-show', name: 'edit-profile');
    }

    public function update(): void
    {
        $profile = UserProfile::query()->findOrFail($this->editingId);

        $this->authorize('update', $profile);

        $updateRequest = new UpdateUserProfileRequest();
        $updateRequest->merge(['user_profile_id' => $profile->id]);

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($updateRequest->rules(), $updateRequest->messages());

        if ($this->address_id !== '') {
            $address = Address::query()->findOrFail($this->address_id);
            $this->authorize('view', $address);
            $validated['address_id'] = (int) $this->address_id;
        } else {
            $validated['address_id'] = null;
        }

        $profile->update($validated);

        $this->dispatch('modal-close', name: 'edit-profile');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $profile = UserProfile::query()->findOrFail($id);

        $this->authorize('delete', $profile);

        $profile->delete();
    }

    public function cancelEdit(): void
    {
        $this->dispatch('modal-close', name: 'edit-profile');
        $this->resetForm();
    }

    /**
     * @return Collection<int, UserProfile>
     */
    #[Computed]
    public function profiles(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->userProfiles()->with(['jurisdiction', 'address', 'entities'])->orderBy('id')->get();
    }

    /**
     * @return Collection<int, Jurisdiction>
     */
    #[Computed]
    public function jurisdictions(): Collection
    {
        return Jurisdiction::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Address>
     */
    #[Computed]
    public function addresses(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        return Address::query()->where('user_id', $user->id)->orderBy('street')->get();
    }

    public function render(): View
    {
        return view('livewire.management.user-profiles');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->tax_id = '';
        $this->jurisdiction_id = '';
        $this->address_id = '';
        $this->resetValidation();
    }
}
