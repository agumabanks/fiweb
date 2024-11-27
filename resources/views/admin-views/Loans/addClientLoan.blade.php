@extends('layouts.admin.app')

@section('title', translate('Add Client Loan'))

@section('content')
<div class="container-fluid content">
    <div class="row g-3 mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    {{-- Loan Form --}}
                    <h5 class="card-title text-muted">{{ translate('Add Loan for Client') }}</h5>
                    <form action="{{ route('admin.loans.storeClientLoan') }}" method="POST">
                        @csrf
                        <div class="row">
                            {{-- Client Name --}}
                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">{{ translate('Client Name') }}</label>
                                <input type="text" class="form-control" id="client_name" value="{{ $client->name }}" disabled>
                            </div>

                            {{-- Client ID (Hidden Field) --}}
                            <input type="hidden" name="client_id" value="{{ $client->id }}">

                            {{-- Agent Selection --}}
                            <div class="col-md-6 mb-3">
                                <label for="agent_id" class="form-label">{{ translate('Loan Owner (Agent)') }}</label>
                                <select class="form-control" id="agent_id" name="agent_id" required>
                                    <option value="" disabled selected>{{ translate('Select Agent') }}</option>
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->f_name }} {{ $agent->l_name }} ({{ translate('Clients Managed') }}: {{ $agent->client_count }}, {{ translate('Total Money Out') }}: {{ number_format($agent->total_money_out, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plan Selection --}}
                            <div class="col-md-6 mb-3">
                                <label for="plan_id" class="form-label">{{ translate('Select Loan Plan') }}</label>
                                <select class="form-control" id="plan_id" name="plan_id" required>
                                    <option value="" disabled selected>{{ translate('Select Loan Plan') }}</option>
                                    @foreach ($loanPlans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->plan_name }} ({{ translate('Min') }}: {{ number_format($plan->min_amount, 0) }}, {{ translate('Max') }}: {{ number_format($plan->max_amount, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Loan Amount --}}
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">{{ translate('Loan Amount') }}</label>
                                <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Installment Interval --}}
                            <div class="col-md-6 mb-3">
                                <label for="installment_interval" class="form-label">{{ translate('Installment Interval (Days)') }}</label>
                                <input type="number" class="form-control" id="installment_interval" name="installment_interval" value="{{ old('installment_interval') }}" required>
                                @error('installment_interval')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Paid Amount --}}
                            <div class="col-md-6 mb-3">
                                <label for="paid_amount" class="form-label">{{ translate('Paid Amount') }}</label>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount') }}">
                                @error('paid_amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Loan Taken Date --}}
                            <div class="col-md-6 mb-3">
                                <label for="taken_date" class="form-label">{{ translate('Loan Taken Date') }}</label>
                                <input type="date" class="form-control" id="taken_date" name="taken_date" value="{{ old('taken_date') }}">
                                @error('taken_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Save Button --}}
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-lg">{{ translate('Add Loan') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
