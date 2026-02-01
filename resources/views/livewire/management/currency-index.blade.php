<div>
    <flux:header heading="Currency Management">
        <flux:subheading>
            Manage currencies and exchange rates
        </flux:subheading>
    </flux:header>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Code</flux:table.column>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Symbol</flux:table.column>
            <flux:table.column>Decimal Places</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($currencies as $currency)
                <flux:table.row :key="$currency->id">
                    <flux:table.cell>{{ $currency->code }}</flux:table.cell>
                    <flux:table.cell>{{ $currency->name }}</flux:table.cell>
                    <flux:table.cell>{{ $currency->symbol }}</flux:table.cell>
                    <flux:table.cell>{{ $currency->decimal_places }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" :href="route('management.currencies.show', $currency)">
                            View Rates
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach>
        </flux:table.rows>
    </flux:table>
</div>
