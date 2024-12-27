@extends('layouts.admin.app')

@section('title', translate('Agent Client Details'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <h2 class="page-header-title">
                {{ translate('Clients of Agent: ') }} {{ $agent->f_name }} {{ $agent->l_name }} 
                ({{ translate('Total Credit Balance: ') }} {{ number_format($totalCreditBalance, 2) }},
                {{ translate('Total Loan Balance: ') }} {{ number_format($totalLoanBalance, 2) }})
            </h2>
        </div>

        <div class="card card-body">
            <table class="table table-bordered">
                <thead class="bg-success text-white">
                    <tr>
                        <th>{{ translate('Client Name') }}</th>
                        <th>{{ translate('Guarantor Name') }}</th> {{-- Updated column header --}}
                        <th>{{ translate('Phone') }}</th>
                        <th>{{ translate('NIN') }}</th>
                        <th>{{ translate('Business') }}</th>
                        <th>{{ translate('Credit Balance') }}</th>
                        <th>{{ translate('Loan Balance') }}</th>
                        <th>{{ translate('Savings Balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientsData as $data) {{-- Iterate over $clientsData --}}
                        <tr>
                            <td>{{ $data['client']->name }}</td>
                            <td>{{ $data['guarantor_name'] }}</td> {{-- Display guarantor name --}}
                            <td>{{ $data['client']->phone }}</td>
                            <td>{{ $data['client']->nin }}</td>
                            <td>{{ $data['client']->business }}</td>
                            <td>{{ number_format($data['client']->credit_balance, 2) }}</td>
                            <td>{{ number_format($data['client']->loan_balance, 2) }}</td>
                            <td>{{ number_format($data['client']->savings_balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
             <div class="table-responsive mt-4 px-3">
        <div class="d-flex justify-content-end">
            {!! $clients->links() !!}
            <nav id="datatablePagination" aria-label="Activity pagination"></nav>
        </div>
    </div>
        </div>
    </div>
@endsection