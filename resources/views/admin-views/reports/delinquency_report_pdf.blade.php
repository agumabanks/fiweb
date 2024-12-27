<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ translate('Delinquency Report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #dddddd; text-align: left; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>{{ translate('Delinquency Metrics and Aging Report') }}</h2>
    <p>{{ translate('Date Range') }}: {{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</p>

    <h3>{{ translate('Summary Metrics') }}</h3>
    <table>
        <tr>
            <th>{{ translate('Total Loans') }}</th>
            <td>{{ $summary['totalLoans'] }}</td>
        </tr>
        <tr>
            <th>{{ translate('Delinquent Loans') }}</th>
            <td>{{ $summary['totalDelinquent'] }}</td>
        </tr>
    </table>

    <h3>{{ translate('Aging Distribution') }}</h3>
    <table>
        <tr>
            <th>{{ translate('Category') }}</th>
            <th>{{ translate('Count') }}</th>
        </tr>
        @foreach($agingData as $category => $count)
            <tr>
                <td>{{ translate($category) }}</td>
                <td>{{ $count }}</td>
            </tr>
        @endforeach
    </table>

    <h3>{{ translate('Delinquency Trends') }}</h3>
    <table>
        <tr>
            <th>{{ translate('Month') }}</th>
            <th>{{ translate('Delinquent Loans') }}</th>
        </tr>
        @foreach($trends as $trend)
            <tr>
                <td>{{ $trend['month'] }}</td>
                <td>{{ $trend['count'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3>{{ translate('Agent Performance') }}</h3>
    <table>
        <tr>
            <th>{{ translate('Agent') }}</th>
            <th>{{ translate('Delinquent Loans') }}</th>
            <th>{{ translate('Total Loans') }}</th>
            <th>{{ translate('Delinquency Rate (%)') }}</th>
        </tr>
        @foreach($agentPerformance as $agent)
            <tr>
                <td>{{ $agent['agent'] }}</td>
                <td>{{ $agent['delinquent'] }}</td>
                <td>{{ $agent['total'] }}</td>
                <td>{{ $agent['rate'] }}</td>
            </tr>
        @endforeach
    </table>

    <h3>{{ translate('Delinquent Loans Details') }}</h3>
    <table>
        <thead>
            <tr>
                <th>{{ translate('Client Name') }}</th>
                <th>{{ translate('Loan Amount') }}</th>
                <th>{{ translate('Due Date') }}</th>
                <th>{{ translate('Days Overdue') }}</th>
                <th>{{ translate('Agent') }}</th>
                <th>{{ translate('Follow-Up Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $loan)
                <tr>
                    <td>{{ $loan['client_name'] }}</td>
                    <td>UGX {{ number_format($loan['loan_amount'], 0) }}</td>
                    <td>{{ $loan['due_date'] }}</td>
                    <td>{{ $loan['days_overdue'] }}</td>
                    <td>{{ $loan['agent_name'] }}</td>
                    <td>{{ $loan['follow_up_status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
