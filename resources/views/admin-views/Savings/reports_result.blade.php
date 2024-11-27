@extends('layouts.admin.app')

@section('title', 'Report: ' . $reportTitle)

@push('css_or_js')
    <!-- Include Chart.js for visual analytics (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Additional styling if needed */
    </style>
@endpush

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $reportTitle }}</h1>
        <p class="welcome-msg">Reports from {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($reportType === 'total_deposits' || $reportType === 'total_withdrawals')
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Client Name</th>
                            @if ($reportType === 'total_deposits')
                                <th>Total Deposited</th>
                            @elseif ($reportType === 'total_withdrawals')
                                <th>Total Withdrawn</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $record)
                            <tr>
                                <td>{{ $record->savingsAccount->account_number }}</td>
                                <td>{{ $record->savingsAccount->client->name }}</td>
                                @if ($reportType === 'total_deposits')
                                    <td>${{ number_format($record->total_deposited, 2) }}</td>
                                @elseif ($reportType === 'total_withdrawals')
                                    <td>${{ number_format($record->total_withdrawn, 2) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif ($reportType === 'interest_earned')
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Account Type</th>
                            <th>Description</th>
                            <th>Interest Rate (%)</th>
                            <th>Total Interest Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $type)
                            <tr>
                                <td>{{ $type['name'] }}</td>
                                <td>{{ $type['description'] }}</td>
                                <td>{{ number_format($type['interest_rate'], 2) }}</td>
                                <td>${{ number_format($type['total_interest_earned'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif ($reportType === 'monthly_growth')
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Client Name</th>
                            <th>Total Deposits</th>
                            <th>Total Withdrawals</th>
                            <th>Net Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $record)
                            <tr>
                                <td>{{ $record['account_number'] }}</td>
                                <td>{{ $record['client_name'] }}</td>
                                <td>${{ number_format($record['total_deposits'], 2) }}</td>
                                <td>${{ number_format($record['total_withdrawals'], 2) }}</td>
                                <td>${{ number_format($record['net_growth'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Example Chart: Net Growth per Account -->
                <canvas id="netGrowthChart" width="400" height="200"></canvas>

                <script>
                    var ctx = document.getElementById('netGrowthChart').getContext('2d');
                    var netGrowthChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($data->pluck('account_number')) !!},
                            datasets: [{
                                label: 'Net Growth ($)',
                                data: {!! json_encode($data->pluck('net_growth')) !!},
                                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                </script>

            @elseif ($reportType === 'top_performing')
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Client Name</th>
                            <th>Total Deposited</th>
                            <th>Total Withdrawn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $account)
                            <tr>
                                <td>{{ $account->account_number }}</td>
                                <td>{{ $account->client->name }}</td>
                                <td>${{ number_format($account->total_deposited, 2) }}</td>
                                <td>${{ number_format($account->total_withdrawn, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <!-- Back to Reports -->
            <div class="mt-3">
                <a href="{{ route('admin.savings.reports.index') }}" class="btn btn-secondary">Back to Reports</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Include Chart.js for visual analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Additional Scripts if needed -->
@endpush
