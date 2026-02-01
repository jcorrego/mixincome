<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Models\Currency;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

final class CurrencyIndex extends Component
{
    /**
     * Render the component.
     */
    public function render(): View
    {
        /** @var Collection<int, Currency> $currencies */
        $currencies = Currency::query()->orderBy('code')->get();

        return view('livewire.management.currency-index', [
            'currencies' => $currencies,
        ]);
    }
}
