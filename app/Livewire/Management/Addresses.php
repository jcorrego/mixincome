<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Addresses extends Component
{
    public string $street = '';

    public string $city = '';

    public string $state = '';

    public string $postal_code = '';

    public string $country = '';

    public ?int $editingId = null;

    public function create(): void
    {
        $storeRequest = new StoreAddressRequest();

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($storeRequest->rules(), $storeRequest->messages());

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $user->addresses()->create($validated);

        $this->dispatch('modal-close', name: 'create-address');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $address = Address::query()->findOrFail($id);

        $this->authorize('update', $address);

        $this->editingId = $address->id;
        $this->street = $address->street;
        $this->city = $address->city;
        $this->state = $address->state;
        $this->postal_code = $address->postal_code;
        $this->country = $address->country;

        $this->dispatch('modal-show', name: 'edit-address');
    }

    public function update(): void
    {
        $address = Address::query()->findOrFail($this->editingId);

        $this->authorize('update', $address);

        $updateRequest = new UpdateAddressRequest();

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($updateRequest->rules(), $updateRequest->messages());

        $address->update($validated);

        $this->dispatch('modal-close', name: 'edit-address');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $address = Address::query()->findOrFail($id);

        $this->authorize('delete', $address);

        $address->delete();
    }

    public function cancelEdit(): void
    {
        $this->dispatch('modal-close', name: 'edit-address');
        $this->resetForm();
    }

    /**
     * @return Collection<int, Address>
     */
    #[Computed]
    public function addresses(): Collection
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return $user->addresses()->with(['userProfiles', 'entities'])->orderBy('street')->get();
    }

    public function render(): View
    {
        return view('livewire.management.addresses');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->street = '';
        $this->city = '';
        $this->state = '';
        $this->postal_code = '';
        $this->country = '';
        $this->resetValidation();
    }
}
