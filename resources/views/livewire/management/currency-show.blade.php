<div>
    <flux:header :heading="$currency->name . ' (' . $currency->code . ')'" :back-link="route('management.currencies.index')">
        <flux:subheading>
            Manage exchange rates for {{ $currency->symbol }} {{ $currency->code }}
        </flux:subheading>
    </flux:header>

    {{-- Fetch Rate Form --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">Fetch New Exchange Rate</flux:heading>

        <form wire:submit="fetchRate">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <flux:field>
                    <flux:label>From Currency</flux:label>
                    <flux:select wire:model="fromCurrencyId" name="fromCurrencyId">
                        <option value="">Select currency</option>
                        @foreach ($allCurrencies as $curr)
                            <option value="{{ $curr->id }}">{{ $curr->code }} - {{ $curr->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="fromCurrencyId" />
                </flux:field>

                <flux:field>
                    <flux:label>To Currency</flux:label>
                    <flux:select wire:model="toCurrencyId" name="toCurrencyId">
                        <option value="">Select currency</option>
                        @foreach ($allCurrencies as $curr)
                            <option value="{{ $curr->id }}">{{ $curr->code }} - {{ $curr->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="toCurrencyId" />
                </flux:field>

                <flux:field>
                    <flux:label>Date</flux:label>
                    <flux:input wire:model="date" name="date" type="date" />
                    <flux:error name="date" />
                </flux:field>
            </div>

            <flux:button type="submit" class="mt-4" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="fetchRate">Fetch Rate</span>
                <span wire:loading wire:target="fetchRate">Fetching...</span>
            </flux:button>
        </form>
    </flux:card>

    {{-- Source Rates (this currency as FROM) --}}
    <flux:card class="mb-6">
        <flux:heading size="lg" class="mb-4">
            Exchange Rates FROM {{ $currency->code }}
        </flux:heading>

        @if ($currency->sourceFxRates->isEmpty())
            <flux:text class="text-zinc-500">No exchange rates found where {{ $currency->code }} is the source currency.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>To Currency</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Rate</flux:table.column>
                    <flux:table.column>Source</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($currency->sourceFxRates as $rate)
                        <flux:table.row :key="$rate->id">
                            <flux:table.cell>{{ $rate->toCurrency->code }} - {{ $rate->toCurrency->name }}</flux:table.cell>
                            <flux:table.cell>{{ $rate->date->toDateString() }}</flux:table.cell>
                            <flux:table.cell>{{ $rate->rate }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$rate->source === 'ecb' ? 'green' : 'blue'">
                                    {{ strtoupper($rate->source) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                @if ($rate->source === 'ecb')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        wire:click="refetchRate({{ $rate->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="refetchRate({{ $rate->id }})"
                                    >
                                        <span wire:loading.remove wire:target="refetchRate({{ $rate->id }})">Refetch</span>
                                        <span wire:loading wire:target="refetchRate({{ $rate->id }})">...</span>
                                    </flux:button>
                                @else
                                    <flux:text class="text-sm text-zinc-500">Manual entry</flux:text>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Target Rates (this currency as TO) --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">
            Exchange Rates TO {{ $currency->code }}
        </flux:heading>

        @if ($currency->targetFxRates->isEmpty())
            <flux:text class="text-zinc-500">No exchange rates found where {{ $currency->code }} is the target currency.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>From Currency</flux:table.column>
                    <flux:table.column>Date</flux:table.column>
                    <flux:table.column>Rate</flux:table.column>
                    <flux:table.column>Source</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($currency->targetFxRates as $rate)
                        <flux:table.row :key="$rate->id">
                            <flux:table.cell>{{ $rate->fromCurrency->code }} - {{ $rate->fromCurrency->name }}</flux:table.cell>
                            <flux:table.cell>{{ $rate->date->toDateString() }}</flux:table.cell>
                            <flux:table.cell>{{ $rate->rate }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$rate->source === 'ecb' ? 'green' : 'blue'">
                                    {{ strtoupper($rate->source) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                @if ($rate->source === 'ecb')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        wire:click="refetchRate({{ $rate->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="refetchRate({{ $rate->id }})"
                                    >
                                        <span wire:loading.remove wire:target="refetchRate({{ $rate->id }})">Refetch</span>
                                        <span wire:loading wire:target="refetchRate({{ $rate->id }})">...</span>
                                    </flux:button>
                                @else
                                    <flux:text class="text-sm text-zinc-500">Manual entry</flux:text>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
