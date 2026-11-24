<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Vehicle;
use App\Support\Para;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SatisController extends Controller
{
    public function store(Request $request, Vehicle $arac): RedirectResponse
    {
        $request->merge([
            'sale_amount' => $request->filled('sale_amount') ? str_replace(['.', ' '], '', str_replace(',', '.', (string) $request->input('sale_amount'))) : null,
            'sale_rate' => $request->filled('sale_rate') ? str_replace(',', '.', (string) $request->input('sale_rate')) : null,
        ]);

        $data = $request->validate([
            'sold_on' => ['required', 'date'],
            'buyer_name' => ['nullable', 'string', 'max:120'],
            'sale_amount' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'sale_currency' => ['required', Rule::in(array_keys(Para::CURRENCIES))],
            'sale_rate' => ['nullable', 'numeric', 'gt:0'],
            'is_trade_in' => ['nullable', 'boolean'],
        ], [
            'sold_on.required' => 'Satış tarihini yazın.',
            'sale_amount.required' => 'Satış fiyatını yazın.',
            'sale_amount.numeric' => 'Satış fiyatını sayı olarak yazın.',
        ]);

        $soldOn = \Illuminate\Support\Carbon::parse($data['sold_on']);
        $rate = $data['sale_rate'] ?? ExchangeRate::rateFor($data['sale_currency'], $soldOn);
        if ($rate === null) {
            return back()->withErrors(['sale_rate' => Para::CURRENCIES[$data['sale_currency']].' için kur bulunamadı, kuru elle yazın.']);
        }

        $arac->update([
            'status' => 'satildi',
            'sold_on' => $data['sold_on'],
            'buyer_name' => $data['buyer_name'] ?? null,
            'sale_amount' => $data['sale_amount'],
            'sale_currency' => $data['sale_currency'],
            'sale_rate' => $rate,
            'sale_base' => round((float) $data['sale_amount'] * (float) $rate, 2),
            'is_trade_in' => $request->boolean('is_trade_in'),
        ]);

        return redirect()->route('araclar.show', $arac)->with('ok', 'Satış kaydedildi.');
    }

    public function destroy(Vehicle $arac): RedirectResponse
    {
        $arac->update([
            'status' => 'stokta',
            'sold_on' => null, 'buyer_name' => null, 'sale_amount' => null,
            'sale_currency' => null, 'sale_rate' => null, 'sale_base' => null, 'is_trade_in' => false,
        ]);

        return redirect()->route('araclar.show', $arac)->with('ok', 'Satış kaydı geri alındı, araç stoğa döndü.');
    }
}
