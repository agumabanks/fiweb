@if ($clientLoanPayHistroy && count($clientLoanPayHistroy) > 0)
    <div class="table-responsive">
        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Amount Paid</th>
                                    <th>Credit Balance</th>
                                    <th>Note</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientLoanPayHistroy as $payment)
                                <tr>
                                    @if(!$payment->is_reversed)
                                        <td>{{ $payment->payment_date }}</td>
                                        <td>{{ number_format($payment->amount, 0) }} /=</td>
                                        <td>{{ number_format($payment->credit_balance, 0) }} /=</td>
                                        <td>{{ $payment->note }}</td>
                                    @else
                                        <td style="color: red; text-decoration: line-through;">{{ $payment->payment_date }}</td>
                                        <td style="color: red; text-decoration: line-through;">{{ number_format($payment->amount, 0) }} /=</td>
                                        <td style="color: red; text-decoration: line-through;">{{ number_format($payment->credit_balance, 0) }} /=</td>
                                        <td style="color: red; text-decoration: line-through;">{{ $payment->note }}</td>
                                    @endif
                                
                                    <td>
                                        @if(!$payment->is_reversed)
                                            <form action="{{ route('admin.payments.reverse', $payment->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-undo-alt"></i> Reverse
                                                </button>     
                                            </form>
                                        @else
                                            <span class="text-danger">Reversed</span>
                                        @endif
                                
                                        @if(!$payment->is_reversed)
                                            <a href="{{ route('admin.transaction.show', $payment->id) }}" class="btn btn-primary btn-sm" title="View Receipt">
                                                Print Receipt
                                            </a>
                                
                                            <form action="{{ route('admin.transaction.sms', $payment->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Send SMS">
                                                   Send SMS
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                
                                @endforeach
                            </tbody>
                        </table>
    </div>
@else
    <p class="text-muted">No transactions found.</p>
@endif










{{-- <div class="tab-pane fade" id="transaction-history" role="tabpanel" aria-labelledby="transaction-history-tab">
                    @if ($clientLoanPayHistroy && count($clientLoanPayHistroy) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Payment Date</th>
                                    <th>Amount Paid</th>
                                    <th>Credit Balance</th>
                                    <th>Note</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientLoanPayHistroy as $payment)
                                <tr>
                                    @if(!$payment->is_reversed)
                                        <td>{{ $payment->payment_date }}</td>
                                        <td>{{ number_format($payment->amount, 0) }} /=</td>
                                        <td>{{ number_format($payment->credit_balance, 0) }} /=</td>
                                        <td>{{ $payment->note }}</td>
                                    @else
                                        <td style="color: red; text-decoration: line-through;">{{ $payment->payment_date }}</td>
                                        <td style="color: red; text-decoration: line-through;">{{ number_format($payment->amount, 0) }} /=</td>
                                        <td style="color: red; text-decoration: line-through;">{{ number_format($payment->credit_balance, 0) }} /=</td>
                                        <td style="color: red; text-decoration: line-through;">{{ $payment->note }}</td>
                                    @endif
                                
                                    <td>
                                        @if(!$payment->is_reversed)
                                            <form action="{{ route('admin.payments.reverse', $payment->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-undo-alt"></i> Reverse
                                                </button>     
                                            </form>
                                        @else
                                            <span class="text-danger">Reversed</span>
                                        @endif
                                
                                        @if(!$payment->is_reversed)
                                            <a href="{{ route('admin.transaction.show', $payment->id) }}" class="btn btn-primary btn-sm" title="View Receipt">
                                                Print Receipt
                                            </a>
                                
                                            <form action="{{ route('admin.transaction.sms', $payment->id) }}" method="POST" class="d-inline-block">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Send SMS">
                                                   Send SMS
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No transactions found.</p>
                    @endif
                </div> --}}