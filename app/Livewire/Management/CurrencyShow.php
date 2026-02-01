<?php

declare(strict_types=1);

namespace App\Livewire\Management;

use App\Exceptions\FxRateException;
use App\Models\Currency;
use App\Models\FxRate;
use App\Services\FxRateService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Date;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class CurrencyShow extends Component
{
    public Currency $currency;

    #[Validate('required|exists:currencies,id')]
    public int $fromCurrencyId = 0;

    #[Validate('required|exists:currencies,id|different:fromCurrencyId')]
    public int $toCurrencyId = 0;

    #[Validate('required|date|before_or_equal:today')]
    public string $date = '';

    /**
     * Mount the component with the currency.
     */
    public function mount(Currency $currency): void
    {
        $this->currency = $currency->load(['sourceFxRates.toCurrency', 'targetFxRates.fromCurrency']);
        $this->fromCurrencyId = $currency->id;
        $this->date = Date::today()->toDateString();
    }

    /**
     * Fetch a new exchange rate manually.
     */
    public function fetchRate(FxRateService $fxRateService): void
    {
        $this->validate();

        try {
            $fromCurrency = Currency::query()->findOrFail($this->fromCurrencyId);
            $toCurrency = Currency::query()->findOrFail($this->toCurrencyId);
            $date = Date::parse($this->date);

            $rate = $fxRateService->fetchRateManual($fromCurrency, $toCurrency, $date);

            $this->dispatch('rate-fetched', message: "Rate fetched successfully: {$rate->rate} on {$rate->date->toDateString()}");

            // Refresh the currency with updated rates
            $this->currency->refresh()->load(['sourceFxRates.toCurrency', 'targetFxRates.fromCurrency']);

            // Reset form
            $this->reset(['toCurrencyId', 'date']);
            $this->fromCurrencyId = $this->currency->id;
            $this->date = Date::today()->toDateString();
        } catch (FxRateException $e) {
            $this->addError('fetchRate', $e->getMessage());
        }
    }

    /**
     * Re-fetch an existing exchange rate from ECB.
     */
    public function refetchRate(int $fxRateId, FxRateService $fxRateService): void
    {
        try {
            $rate = FxRate::query()->findOrFail($fxRateId);
            $oldRate = $rate->rate;

            $updatedRate = $fxRateService->refetchRate($rate);

            if ((float) $oldRate !== (float) $updatedRate->rate) {
                $message = "Rate updated from {$oldRate} to {$updatedRate->rate}";
            } else {
                $message = 'Rate unchanged, ECB value matches existing rate';
            }

            $this->dispatch('rate-refetched', message: $message);

            // Refresh the currency with updated rates
            $this->currency->refresh()->load(['sourceFxRates.toCurrency', 'targetFxRates.fromCurrency']);
        } catch (FxRateException $e) {
            $this->addError('refetchRate', $e->getMessage());
        }
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $allCurrencies = Currency::query()->orderBy('code')->get();

        return view('livewire.management.currency-show', [
            'allCurrencies' => $allCurrencies,
        ]);
    }
}
