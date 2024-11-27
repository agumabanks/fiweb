<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;


use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientsExport implements FromQuery, WithHeadings
{
    protected $request;

    /**
     * Constructor to accept request parameters.
     *
     * @param  array  $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Return a query of clients.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Client::query();

        // Apply filters based on request parameters
        if (!empty($this->request['search'])) {
            $search = $this->request['search'];
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        }

        // Add other filters as needed

        return $query->select(
            'id',
            'name',
            'email',
            'phone',
            'address',
            'status',
            'kyc_verified_at',
            'dob',
            'business',
            'nin',
            'credit_balance',
            'savings_balance',
            'added_by',
            'next_of_kin',
            'branch_id'
        );
    }

    /**
     * Return the headings for the Excel sheet.
     *
     * @return array
     */
    public function headings(): array
    {
        // Same as before
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Address',
            'Status',
            'KYC Verified At',
            'Date of Birth',
            'Business',
            'NIN',
            'Credit Balance',
            'Savings Balance',
            
        ];
    }
}
