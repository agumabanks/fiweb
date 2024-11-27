@extends('layouts.admin.app')

@section('title', $pageTitle)

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="container-fluid content">
        <!-- Page Header -->
        <h2 class="my-4 text-primary">
            {{ $pageTitle }}
            <span class="badge bg-primary text-white">{{ $totalLoans }}</span>
        </h2>

        <!-- Loans Table -->
        @if ($loans->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle shadow-sm">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th>{{ translate('Client Name') }}</th>
                            <th>{{ translate('Agent') }}</th>
                            <th>{{ translate('Plan') }}</th>
                            <th>{{ translate('Amount') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th class="text-center">{{ translate('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            <tr>
                                <!-- Client Name -->
                                <td>{{ $loan->client->name ?? translate('No Client') }}</td>

                                <!-- Agent Name -->
                                <td>{{ $loan->agent->f_name }} {{ $loan->agent->l_name }}</td>

                                <!-- Loan Plan Name -->
                                <td>{{ $loan->plan->plan_name }}</td>

                                <!-- Loan Amount -->
                                <td>{{ number_format($loan->amount, 0) }} /=</td>

                                <!-- Loan Status -->
                                <td>
                                    <span class="badge p-2
                                        @switch($loan->status)
                                            @case(0) bg-warning @break
                                            @case(1) bg-primary @break
                                            @case(2) bg-success @break
                                            @case(3) bg-danger @break
                                            @default bg-secondary
                                        @endswitch">
                                        @switch($loan->status)
                                            @case(0) {{ translate('Pending') }} @break
                                            @case(1) {{ translate('Running') }} @break
                                            @case(2) {{ translate('Paid') }} @break
                                            @case(3) {{ translate('Rejected') }} @break
                                            @default {{ translate('Unknown Status') }}
                                        @endswitch
                                    </span>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <!-- View Loan -->
                                        <a class="btn btn-outline-primary" href="{{ route('admin.loans.show', $loan->id) }}" title="{{ translate('View Loan') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>

                                        <!-- Edit Loan -->
                                        <a class="btn btn-outline-info" href="{{ route('admin.loans.loanedit', $loan->id) }}" title="{{ translate('Edit Loan') }}">
                                            <i class="fa fa-pencil"></i>
                                        </a>

                                        <!-- Delete Loan -->
                                        <form action="{{ route('admin.loan.deleteLoan', $loan->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this loan?') }}');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger" title="{{ translate('Delete Loan') }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $loans->links() }}
            </div>
        @else
            <!-- No Loans Message -->
            <div class="alert alert-info text-center">
                {{ $emptyMessage }}
            </div>
        @endif
    </div>
@endsection

@push('script')
@endpush

@push('script_2')
@endpush
