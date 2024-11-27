<?php

namespace App\Http\Controllers;

use App\Models\ExcessFund;
use Illuminate\Http\Request;

class ExcessFundController extends Controller
{
    public function index()
    {
        $excessFunds = ExcessFund::with('client')->get();
        return view('excess_funds.index', compact('excessFunds'));
    }

    public function create()
    {
        $clients = Client::all();
        return view('excess_funds.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'date_added' => 'required|date',
            'status' => 'required|in:unallocated,allocated',
        ]);

        ExcessFund::create($validated);

        return redirect()->route('excess-funds.index')->with('success', 'Excess fund added successfully.');
    }

    public function show(ExcessFund $excessFund)
    {
        return view('excess_funds.show', compact('excessFund'));
    }

    public function edit(ExcessFund $excessFund)
    {
        $clients = Client::all();
        return view('excess_funds.edit', compact('excessFund', 'clients'));
    }

    public function update(Request $request, ExcessFund $excessFund)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0',
            'date_added' => 'required|date',
            'status' => 'required|in:unallocated,allocated',
        ]);

        $excessFund->update($validated);

        return redirect()->route('excess-funds.index')->with('success', 'Excess fund updated successfully.');
    }

    public function destroy(ExcessFund $excessFund)
    {
        $excessFund->delete();

        return redirect()->route('excess-funds.index')->with('success', 'Excess fund deleted successfully.');
    }
}
