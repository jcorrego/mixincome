<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Entities') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Manage your legal entities (LLC, Corp, Partnership, etc.).') }}</flux:text>
        </div>

        @unless ($editingId)
            <flux:modal.trigger name="create-entity">
                <flux:button variant="primary" icon="plus" :disabled="$this->profiles->isEmpty()">{{ __('Add Entity') }}</flux:button>
            </flux:modal.trigger>
        @endunless
    </div>

    @if ($this->profiles->isEmpty())
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('No tax profiles yet') }}</flux:callout.heading>
            <flux:callout.text>{{ __('You need to create a tax profile before adding entities.') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Entities Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Type') }}</flux:table.column>
            <flux:table.column>{{ __('Tax ID') }}</flux:table.column>
            <flux:table.column>{{ __('Profile') }}</flux:table.column>
            <flux:table.column>{{ __('Address') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->entities as $entity)
                <flux:table.row :key="$entity->id">
                    <flux:table.cell variant="strong">{{ $entity->name }}</flux:table.cell>
                    <flux:table.cell>{{ $entity->entity_type->value }}</flux:table.cell>
                    <flux:table.cell>{{ $entity->tax_id }}</flux:table.cell>
                    <flux:table.cell>{{ $entity->userProfile->jurisdiction->name }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($entity->address)
                            {{ $entity->address->city }}, {{ $entity->address->country }}
                        @else
                            <flux:text class="text-zinc-400">{{ __('None') }}</flux:text>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" :variant="$entity->status === 'Active' ? 'pill' : 'outline'">
                            {{ $entity->status }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button variant="ghost" size="sm" icon="pencil" wire:click="edit({{ $entity->id }})" />
                            <flux:button variant="ghost" size="sm" icon="trash" wire:click="delete({{ $entity->id }})" wire:confirm="{{ __('Are you sure you want to delete this entity?') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    {{-- Create Modal --}}
    <flux:modal name="create-entity" class="md:w-96">
        <form wire:submit="create" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Create Entity') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Add a new legal entity to a tax profile.') }}</flux:text>
            </div>

            <flux:select wire:model="user_profile_id" :label="__('Tax Profile')" placeholder="{{ __('Select a profile') }}">
                @foreach ($this->profiles as $profile)
                    <flux:select.option :value="$profile->id">{{ $profile->jurisdiction->name }} — {{ $profile->tax_id }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="name" :label="__('Entity Name')" placeholder="{{ __('e.g. My LLC') }}" />

            <flux:select wire:model="entity_type" :label="__('Entity Type')" placeholder="{{ __('Select a type') }}">
                @foreach ($this->entityTypes as $type)
                    <flux:select.option :value="$type->value">{{ $type->value }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="tax_id" :label="__('Tax ID (EIN)')" placeholder="{{ __('e.g. 12-3456789') }}" />

            <flux:select wire:model="address_id" :label="__('Address')" placeholder="{{ __('No address') }}">
                <flux:select.option value="">{{ __('No address') }}</flux:select.option>
                @foreach ($this->addresses as $address)
                    <flux:select.option :value="$address->id">{{ $address->displayLabel() }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex">
                <flux:spacer />
                <flux:button type="submit" variant="primary">{{ __('Create') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal name="edit-entity" class="md:w-96">
        <form wire:submit="update" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Edit Entity') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Update the entity details.') }}</flux:text>
            </div>

            <flux:select wire:model="user_profile_id" :label="__('Tax Profile')" placeholder="{{ __('Select a profile') }}">
                @foreach ($this->profiles as $profile)
                    <flux:select.option :value="$profile->id">{{ $profile->jurisdiction->name }} — {{ $profile->tax_id }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="name" :label="__('Entity Name')" />

            <flux:select wire:model="entity_type" :label="__('Entity Type')" placeholder="{{ __('Select a type') }}">
                @foreach ($this->entityTypes as $type)
                    <flux:select.option :value="$type->value">{{ $type->value }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="tax_id" :label="__('Tax ID (EIN)')" />

            <flux:select wire:model="address_id" :label="__('Address')" placeholder="{{ __('No address') }}">
                <flux:select.option value="">{{ __('No address') }}</flux:select.option>
                @foreach ($this->addresses as $address)
                    <flux:select.option :value="$address->id">{{ $address->displayLabel() }}</flux:select.option>
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
