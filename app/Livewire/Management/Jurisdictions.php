<?php

declare(strict_types=1);

namespace App\Livewire\Management;

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
        /** @var array<string, mixed> $validated */
        $validated = $this->validate($this->createRules());

        Jurisdiction::query()->create($validated);

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
    }

    public function update(): void
    {
        $jurisdiction = Jurisdiction::query()->findOrFail($this->editingId);

        /** @var array<string, mixed> $validated */
        $validated = $this->validate($this->updateRules($jurisdiction->id));

        $jurisdiction->update($validated);

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $jurisdiction = Jurisdiction::query()->findOrFail($id);

        $jurisdiction->delete();
    }

    public function cancelEdit(): void
    {
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

    /**
     * @return array<string, array<int, string>>
     */
    private function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'iso_code' => ['required', 'string', 'min:2', 'max:3', 'unique:jurisdictions,iso_code'],
            'timezone' => ['required', 'string', 'timezone'],
            'default_currency' => ['required', 'string', 'size:3'],
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function updateRules(int $ignoreId): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'iso_code' => ['required', 'string', 'min:2', 'max:3', 'unique:jurisdictions,iso_code,'.$ignoreId],
            'timezone' => ['required', 'string', 'timezone'],
            'default_currency' => ['required', 'string', 'size:3'],
        ];
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
