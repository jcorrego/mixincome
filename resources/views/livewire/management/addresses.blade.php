<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Addresses') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage your addresses. Addresses can be linked to profiles and entities.') }}</flux:text>
        </div>

        @unless ($editingId)
            <flux:modal.trigger name="create-address">
                <flux:button variant="primary" icon="plus">{{ __('Add Address') }}</flux:button>
            </flux:modal.trigger>
        @endunless
    </div>

    {{-- Addresses Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Street') }}</flux:table.column>
            <flux:table.column>{{ __('City') }}</flux:table.column>
            <flux:table.column>{{ __('State') }}</flux:table.column>
            <flux:table.column>{{ __('Postal Code') }}</flux:table.column>
            <flux:table.column>{{ __('Country') }}</flux:table.column>
            <flux:table.column>{{ __('Used By') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->addresses as $address)
                <flux:table.row :key="$address->id">
                    <flux:table.cell variant="strong">{{ $address->street }}</flux:table.cell>
                    <flux:table.cell>{{ $address->city }}</flux:table.cell>
                    <flux:table.cell>{{ $address->state }}</flux:table.cell>
                    <flux:table.cell>{{ $address->postal_code }}</flux:table.cell>
                    <flux:table.cell>{{ $address->country }}</flux:table.cell>
                    <flux:table.cell>
                        @php
                            $usageCount = $address->userProfiles->count() + $address->entities->count();
                        @endphp
                        @if ($usageCount > 0)
                            <flux:badge size="sm">{{ $usageCount }} {{ __('link(s)') }}</flux:badge>
                        @else
                            <flux:text class="text-zinc-400">{{ __('None') }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil" wire:click="edit({{ $address->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete({{ $address->id }})" wire:confirm="{{ __('Are you sure you want to delete this address?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Create Modal --}}
    <flux:modal name="create-address" class="md:w-96">
        <form wire:submit="create" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create Address') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Add a new address that can be linked to profiles or entities.') }}</flux:text>
            </div>

            <flux:input wire:model="street" :label="__('Street')" placeholder="{{ __('e.g. 123 Main St') }}" />
            <flux:input wire:model="city" :label="__('City')" placeholder="{{ __('e.g. Miami') }}" />
            <flux:input wire:model="state" :label="__('State / Province')" placeholder="{{ __('e.g. Florida') }}" />
            <flux:input wire:model="postal_code" :label="__('Postal Code')" placeholder="{{ __('e.g. 33101') }}" />
            <flux:input wire:model="country" :label="__('Country')" placeholder="{{ __('e.g. US') }}" />

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal name="edit-address" class="md:w-96">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Address') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Update the address details.') }}</flux:text>
            </div>

            <flux:input wire:model="street" :label="__('Street')" />
            <flux:input wire:model="city" :label="__('City')" />
            <flux:input wire:model="state" :label="__('State / Province')" />
            <flux:input wire:model="postal_code" :label="__('Postal Code')" />
            <flux:input wire:model="country" :label="__('Country')" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button wire:click="cancelEdit" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
