@extends('layouts.admin.app')

@section('title', translate('Agent Report'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <h2 class="page-header-title">{{ translate('Agent Report') }}</h2>
        </div>

        <div class="card card-body">
            <table class="table table-bordered">
                <thead class="bg-success text-white">
                    <tr>
                        <th>{{ translate('Agent Name') }}</th>
                        <th>{{ translate('Number of Clients') }}</th>
                        <th>{{ translate('Total Money Out') }}</th>
                        <th>{{ translate('Expected Daily') }}</th>
                        <th>{{ translate('Amount Collected') }}</th>
                        <th>{{ translate('Performance (%)') }}</th>
                        <th>{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($agents as $agent)
                        <tr>
                            <td>{{ $agent->f_name }} {{ $agent->l_name }}</td>
                            <td>{{ $agent->client_count }}</td>
                            <td>{{ number_format($agent->total_money_out, 2) }}</td>
                            <td>{{ number_format($agent->expected_daily, 2) }}</td>
                            <td>{{ number_format($agent->amount_collected, 2) }}</td>
                            <td>{{ number_format($agent->performance_percentage, 2) }}%</td>
                            <td>
                                <a href="{{ route('admin.agent.client.details', $agent->id) }}" class="btn btn-info">
                                    {{ translate('View') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ translate('No data available') }}</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
    <tr class="font-weight-bold">
        <td>{{ translate('Totals') }}</td>
        <td>{{ $totals['total_clients'] }}</td>
        <td>{{ number_format($totals['total_money_out'], 2) }}</td>
        <td>{{ number_format($totals['total_expected_daily'], 2) }}</td> <!-- Update this line -->
        <td>{{ number_format($totals['total_amount_collected'], 2) }}</td>
        <td>{{ number_format($totals['total_performance'], 2) }}%</td>
        <td></td>
    </tr>
</tfoot>

            </table>
        </div>
    </div>
@endsection
