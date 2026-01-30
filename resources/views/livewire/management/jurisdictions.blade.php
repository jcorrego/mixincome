<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Jurisdictions') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage tax jurisdictions for the platform.') }}</flux:text>
        </div>

        @unless ($editingId)
            <flux:modal.trigger name="create-jurisdiction">
                <flux:button variant="primary" icon="plus">{{ __('Add Jurisdiction') }}</flux:button>
            </flux:modal.trigger>
        @endunless
    </div>

    {{-- Jurisdictions Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('ISO Code') }}</flux:table.column>
            <flux:table.column>{{ __('Timezone') }}</flux:table.column>
            <flux:table.column>{{ __('Currency') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->jurisdictions as $jurisdiction)
                <flux:table.row :key="$jurisdiction->id">
                    <flux:table.cell variant="strong">{{ $jurisdiction->name }}</flux:table.cell>
                    <flux:table.cell>{{ $jurisdiction->iso_code }}</flux:table.cell>
                    <flux:table.cell>{{ $jurisdiction->timezone }}</flux:table.cell>
                    <flux:table.cell>{{ $jurisdiction->default_currency }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil" wire:click="edit({{ $jurisdiction->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete({{ $jurisdiction->id }})" wire:confirm="{{ __('Are you sure you want to delete this jurisdiction?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Create Modal --}}
    <flux:modal name="create-jurisdiction" class="md:w-96">
        <form wire:submit="create" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create Jurisdiction') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Add a new tax jurisdiction to the platform.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" placeholder="e.g. Spain" />
            <flux:input wire:model="iso_code" :label="__('ISO Code')" placeholder="e.g. ES" maxlength="3" />
            <flux:input wire:model="timezone" :label="__('Timezone')" placeholder="e.g. Europe/Madrid" />
            <flux:input wire:model="default_currency" :label="__('Default Currency')" placeholder="e.g. EUR" maxlength="3" />

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal wire:model.self="editingId" class="md:w-96">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Jurisdiction') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Update the jurisdiction details.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" />
            <flux:input wire:model="iso_code" :label="__('ISO Code')" maxlength="3" />
            <flux:input wire:model="timezone" :label="__('Timezone')" />
            <flux:input wire:model="default_currency" :label="__('Default Currency')" maxlength="3" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button wire:click="cancelEdit" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
