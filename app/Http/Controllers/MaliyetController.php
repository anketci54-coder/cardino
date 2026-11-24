<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRate;
use App\Models\Vehicle;
use App\Models\VehicleCost;
use App\Support\Para;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaliyetController extends Controller
{
    public function store(Request $request, Vehicle $arac): RedirectResponse
    {
        $request->merge([
            'amount' => $request->filled('amount') ? str_replace(['.', ' '], '', str_replace(',', '.', (string) $request->input('amount'))) : null,
            'rate' => $request->filled('rate') ? str_replace(',', '.', (string) $request->input('rate')) : null,
        ]);

        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(VehicleCost::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'currency' => ['required', Rule::in(array_keys(Para::CURRENCIES))],
            'rate' => ['nullable', 'numeric', 'gt:0'],
            'spent_on' => ['nullable', 'date'],
        ], [
            'category.required' => 'Masraf türünü seçin.',
            'amount.required' => 'Tutarı yazın.',
            'amount.numeric' => 'Tutarı sayı olarak yazın.',
            'rate.gt' => 'Kur sıfırdan büyük olmalı.',
        ]);

        $spentOn = $data['spent_on'] ? \Illuminate\Support\Carbon::parse($data['spent_on']) : null;
        $rate = $data['rate'] ?? ExchangeRate::rateFor($data['currency'], $spentOn);
        if ($rate === null) {
            return back()->withInput()->withErrors(['rate' => Para::CURRENCIES[$data['currency']].' için kur bulunamadı, kuru elle yazın.']);
        }

        $arac->costs()->create([
            'category' => $data['category'],
            'description' => $data['description'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'rate' => $rate,
            'base_amount' => round((float) $data['amount'] * (float) $rate, 2),
            'spent_on' => $data['spent_on'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('araclar.show', $arac)->with('ok', 'Masraf eklendi.');
    }

    public function destroy(Vehicle $arac, VehicleCost $maliyet): RedirectResponse
    {
        abort_unless($maliyet->vehicle_id === $arac->id, 404);
        $maliyet->delete();

        return redirect()->route('araclar.show', $arac)->with('ok', 'Masraf silindi.');
    }
}
