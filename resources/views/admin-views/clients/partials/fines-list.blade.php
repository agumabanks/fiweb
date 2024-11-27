@if ($client->fines && $client->fines->count() > 0)
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount (UGX)</th>
                <th>Reason</th>
                <th>Note</th>
                <th>Date Added</th>
                <th>Added By</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($client->fines as $fine)
                <tr>
                    <td>{{ $fine->id }}</td>
                    <td>{{ number_format($fine->amount, 2) }}</td>
                    <td>{{ $fine->reason }}</td>
                    <td>{{ $fine->note }}</td>
                    <td>{{ $fine->created_at->format('F d, Y') }}</td>
                    <td>{{ $fine->addedBy->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-muted">No fines recorded for this client.</p>
@endif
