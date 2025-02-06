@if($loans->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    
                    <th>Client</th>
                    <th>Phone</th>
                    <th>Field</th>
                    <th>Amount (/=)</th>
                    <th>Paid (/=)</th>
                    <th>Outstanding (/=)</th>
                    <th>Status</th>
                    <th>Taken Date</th>
                    <th>Due Date</th>
                    {{-- <th>Disbursed At</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach($loans as $loan)
                    <tr>
                        <td>{{ $loan->id }}</td>
                        
                        <td>{{ optional($loan->client)->name ?? 'N/A' }}</td>
                        <td>{{ optional($loan->client)->phone }}</td>
                        <td>{{ optional($loan->agent)->f_name ?? 'N/A' }}</td>
                        <td>{{ number_format($loan->amount, 0) }} </td>
                        <td>{{ number_format($loan->paid_amount, 0) }} </td>
                        <td>{{ number_format($loan->final_amount - $loan->paid_amount, 0) }}</td>
                        <td>
                            @php
                                switch($loan->status) {
                                    case 0: echo '<span class="badge badge-secondary">Pending</span>'; break;
                                    case 1: echo '<span class="badge badge-info">Running</span>'; break;
                                    case 2: echo '<span class="badge badge-success">Paid</span>'; break;
                                    case 3: echo '<span class="badge badge-warning">Overdue</span>'; break;
                                    case 4: echo '<span class="badge badge-danger">Defaulted</span>'; break;
                                    default: echo '<span class="badge badge-light">Unknown</span>';
                                }
                            @endphp
                        </td>
                        <td>{{ $loan->loan_taken_date ? $loan->loan_taken_date->format('Y-m-d') : 'N/A' }}</td>
                        <td>{{ $loan->due_date ? $loan->due_date->format('Y-m-d') : 'N/A' }}</td>
                        {{-- <td>{{ $loan->disbursed_at ? $loan->disbursed_at->format('Y-m-d') : 'N/A' }}</td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $loans->links() }}
    </div>
@else
    <div class="alert alert-info">
        No loans found for the selected criteria.
    </div>
@endif
