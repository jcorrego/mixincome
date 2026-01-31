<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Tax Profiles') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage your tax profiles for each jurisdiction.') }}</flux:text>
        </div>

        @unless ($editingId)
            <flux:modal.trigger name="create-profile">
                <flux:button variant="primary" icon="plus">{{ __('Add Profile') }}</flux:button>
            </flux:modal.trigger>
        @endunless
    </div>

    {{-- Profiles Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Jurisdiction') }}</flux:table.column>
            <flux:table.column>{{ __('Tax ID') }}</flux:table.column>
            <flux:table.column>{{ __('Address') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Entities') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->profiles as $profile)
                <flux:table.row :key="$profile->id">
                    <flux:table.cell variant="strong">{{ $profile->jurisdiction->name }}</flux:table.cell>
                    <flux:table.cell>{{ $profile->tax_id }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($profile->address)
                            {{ $profile->address->city }}, {{ $profile->address->country }}
                        @else
                            <flux:text class="text-zinc-400">{{ __('None') }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :variant="$profile->status === 'Active' ? 'pill' : 'outline'">
                            {{ $profile->status }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $profile->entities->count() }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil" wire:click="edit({{ $profile->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete({{ $profile->id }})" wire:confirm="{{ __('Are you sure you want to delete this profile? This action cannot be undone.') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Create Modal --}}
    <flux:modal name="create-profile" class="md:w-96">
        <form wire:submit="create" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create Tax Profile') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Add a new tax profile for a jurisdiction.') }}</flux:text>
            </div>

            <flux:select wire:model="jurisdiction_id" :label="__('Jurisdiction')" placeholder="{{ __('Select a jurisdiction') }}">
                @foreach ($this->jurisdictions as $jurisdiction)
                    <flux:select.option :value="$jurisdiction->id">{{ $jurisdiction->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="tax_id" :label="__('Tax ID')" placeholder="{{ __('e.g. NIF, SSN, RUT') }}" />

            <flux:select wire:model="address_id" :label="__('Address')" placeholder="{{ __('No address') }}">
                <flux:select.option value="">{{ __('No address') }}</flux:select.option>
                @foreach ($this->addresses as $address)
                    <flux:select.option :value="$address->id">{{ $address->display_label }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal name="edit-profile" class="md:w-96">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Tax Profile') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Update your tax profile details.') }}</flux:text>
            </div>

            <flux:select wire:model="jurisdiction_id" :label="__('Jurisdiction')" placeholder="{{ __('Select a jurisdiction') }}">
                @foreach ($this->jurisdictions as $jurisdiction)
                    <flux:select.option :value="$jurisdiction->id">{{ $jurisdiction->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="tax_id" :label="__('Tax ID')" />

            <flux:select wire:model="address_id" :label="__('Address')" placeholder="{{ __('No address') }}">
                <flux:select.option value="">{{ __('No address') }}</flux:select.option>
                @foreach ($this->addresses as $address)
                    <flux:select.option :value="$address->id">{{ $address->display_label }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button wire:click="cancelEdit" variant="ghost">{{ __('Cancel') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
