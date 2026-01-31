<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Http\Requests\StoreJurisdictionRequest;
use App\Http\Requests\UpdateJurisdictionRequest;
use App\Models\Jurisdiction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class Jurisdictions extends Component
{
    public string $name = '';

    public string $iso_code = '';

    public string $timezone = '';

    public string $default_currency = '';

    public ?int $editingId = null;

    public function create(): void
    {
        $storeRequest = new StoreJurisdictionRequest();

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($storeRequest->rules(), $storeRequest->messages());

        Jurisdiction::query()->create($validated);

        $this->dispatch('modal-close', name: 'create-jurisdiction');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $jurisdiction = Jurisdiction::query()->findOrFail($id);

        $this->editingId = $jurisdiction->id;
        $this->name = $jurisdiction->name;
        $this->iso_code = $jurisdiction->iso_code;
        $this->timezone = $jurisdiction->timezone;
        $this->default_currency = $jurisdiction->default_currency;

        $this->dispatch('modal-show', name: 'edit-jurisdiction');
    }

    public function update(): void
    {
        $jurisdiction = Jurisdiction::query()->findOrFail($this->editingId);

        $updateRequest = new UpdateJurisdictionRequest();
        // Set jurisdiction_id in the request for validation rules
        $updateRequest->merge(['jurisdiction_id' => $jurisdiction->id]);

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($updateRequest->rules(), $updateRequest->messages());

        $jurisdiction->update($validated);

        $this->dispatch('modal-close', name: 'edit-jurisdiction');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $jurisdiction = Jurisdiction::query()->findOrFail($id);

        $jurisdiction->delete();
    }

    public function cancelEdit(): void
    {
        $this->dispatch('modal-close', name: 'edit-jurisdiction');
        $this->resetForm();
    }

    /**
     * @return Collection<int, Jurisdiction>
     */
    #[Computed]
    public function jurisdictions(): Collection
    {
        return Jurisdiction::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.management.jurisdictions');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->iso_code = '';
        $this->timezone = '';
        $this->default_currency = '';
        $this->resetValidation();
    }
}
