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
        @foreach($data['loans'] as $loan)
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
