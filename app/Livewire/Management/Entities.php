<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Enums\EntityType;
use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Models\Address;
use App\Models\Entity;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Entities extends Component
{
    public string $name = '';

    public string $entity_type = '';

    public string $tax_id = '';

    public string $user_profile_id = '';

    public string $address_id = '';

    public ?int $editingId = null;

    public function create(): void
    {
        $storeRequest = new StoreEntityRequest();

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($storeRequest->rules(), $storeRequest->messages());

        // Verify the profile belongs to the current user
        $profile = UserProfile::query()->findOrFail($validated['user_profile_id']);
        $this->authorize('update', $profile);

        if ($this->address_id !== '') {
            $address = Address::query()->findOrFail($this->address_id);
            $this->authorize('view', $address);
            $validated['address_id'] = (int) $this->address_id;
        } else {
            $validated['address_id'] = null;
        }

        Entity::query()->create($validated);

        $this->dispatch('modal-close', name: 'create-entity');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $entity = Entity::query()->findOrFail($id);

        $this->authorize('update', $entity);

        $this->editingId = $entity->id;
        $this->name = $entity->name;
        $this->entity_type = $entity->entity_type->value;
        $this->tax_id = $entity->tax_id;
        $this->user_profile_id = (string) $entity->user_profile_id;
        $this->address_id = (string) ($entity->address_id ?? '');

        $this->dispatch('modal-show', name: 'edit-entity');
    }

    public function update(): void
    {
        $entity = Entity::query()->findOrFail($this->editingId);

        $this->authorize('update', $entity);

        $updateRequest = new UpdateEntityRequest();

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($updateRequest->rules(), $updateRequest->messages());

        if ($this->address_id !== '') {
            $address = Address::query()->findOrFail($this->address_id);
            $this->authorize('view', $address);
            $validated['address_id'] = (int) $this->address_id;
        } else {
            $validated['address_id'] = null;
        }

        $entity->update($validated);

        $this->dispatch('modal-close', name: 'edit-entity');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $entity = Entity::query()->findOrFail($id);

        $this->authorize('delete', $entity);

        $entity->delete();
    }

    public function cancelEdit(): void
    {
        $this->dispatch('modal-close', name: 'edit-entity');
        $this->resetForm();
    }

    /**
     * @return Collection<int, Entity>
     */
    #[Computed]
    public function entities(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        $profileIds = $user->userProfiles()->pluck('id');

        return Entity::query()
            ->whereIn('user_profile_id', $profileIds)
            ->with(['userProfile.jurisdiction', 'address'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, UserProfile>
     */
    #[Computed]
    public function profiles(): Collection
    {
        /** @var User $user */
        $user = auth()->user();

        return $user->userProfiles()->with('jurisdiction')->orderBy('id')->get();
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

    /**
     * @return array<EntityType>
     */
    #[Computed]
    public function entityTypes(): array
    {
        return EntityType::cases();
    }

    public function render(): View
    {
        return view('livewire.management.entities');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->entity_type = '';
        $this->tax_id = '';
        $this->user_profile_id = '';
        $this->address_id = '';
        $this->resetValidation();
    }
}
