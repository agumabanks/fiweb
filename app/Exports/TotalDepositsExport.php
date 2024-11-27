<?php

namespace App\Exports;

use App\Models\SavingsTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TotalDepositsExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return SavingsTransaction::where('type', 'deposit')
                    ->whereBetween('created_at', [$this->startDate, $this->endDate])
                    ->select('savings_account_id', DB::raw('SUM(amount) as total_deposited'))
                    ->groupBy('savings_account_id')
                    ->with('savingsAccount.client')
                    ->get()
                    ->map(function($record) {
                        return [
                            'Account Number' => $record->savingsAccount->account_number,
                            'Client Name' => $record->savingsAccount->client->name,
                            'Total Deposited' => $record->total_deposited,
                        ];
                    });
    }

    public function headings(): array
    {
        return [
            'Account Number',
            'Client Name',
            'Total Deposited',
        ];
    }
}
