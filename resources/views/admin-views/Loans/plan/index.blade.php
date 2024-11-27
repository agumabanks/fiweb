@extends('layouts.admin.app')

@section('title', translate('dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-muted">{{ translate('All Loan Plans') }}</h1>
            <a href="{{ route('admin.add-loan-plans') }}" class="btn btn-primary">{{ translate('+ Add New Plan') }}</a>
        </div>

        <!-- Loan Plans Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ translate('Plan') }}</th>
                            <th>{{ translate('Limit') }}</th>
                            <th>{{ translate('Installment') }}</th>
                            <th>{{ translate('Status') }}</th>
                            <th>{{ translate('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loanPlans as $plan)
                            <tr>
                                <!-- Plan Name and Details -->
                                <td>
                                    <strong>{{ $plan->plan_name }}</strong><br>
                                    <span class="text-success">{{number_format($plan->installment_value, 0)   }}% in {{ number_format( $plan->installment_interval, 0) }} days</span>
                                </td>

                                <!-- Loan Limits -->
                                <td>
                                    <strong>{{ translate('Min:') }}</strong>  {{ number_format($plan->min_amount, 0) }} /=<br>
                                    <strong>{{ translate('Max:') }}</strong>  {{ number_format($plan->max_amount, 0) }}  /=
                                </td>

                                <!-- Installment Details -->
                                <td>
                                    <span class="text-primary">{{number_format($plan->installment_value, 0)  }}% every {{ $plan->installment_interval }} days</span><br>
                                    <span>{{ translate('for') }} {{ $plan->total_installments }} {{ translate('Times') }}</span>
                                </td>

                                <!-- Status -->
                                <td>
                                    @if($plan->status === 'enabled')
                                        <span class="badge bg-success">{{ translate('Enabled') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ translate('Disabled') }}</span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td>
                                    <div class="d-flex justify-content-start gap-2">
                                        <!-- Edit Button -->
                                        <a href="{{ route('admin.loan-plans.edit', $plan->id) }}" class="btn btn-sm btn-dark" title="{{ translate('Edit') }}">
                                            <i class="tio-edit"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.loan-plans.destroy', $plan->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this plan?') }}');" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="{{ translate('Delete') }}">
                                                <i class="tio-delete"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
<!-- Add any custom scripts here if needed -->
@endpush

@push('script_2')
<!-- Add any secondary custom scripts here if needed -->
@endpush
