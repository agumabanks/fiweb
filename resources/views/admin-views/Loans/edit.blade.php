@extends('layouts.admin.app')

@section('title', translate('Edit Loan'))

@section('content')
<div class="container-fluid content">
    {{-- Wallet and Personal Info --}}
    
    <div class="row g-3 mt-4">
        <div class="col-md-12">
            @if($loan)
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        {{-- Client and Loan Status --}}
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-6">
                                <div class="col-md-6 text-end"> 
                                    <img src="{{ $client->client_photo ? "https://lendsup.sanaa.co/public/assets/admin/img/160x160/img1.jpg" : "https://lendsup.sanaa.co/public/assets/admin/img/160x160/img1.jpg" }}" 
                                         alt="Client Photo" 
                                         class="rounded-circle border img-thumbnail mb-2" 
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                    
                                </div>
                                <h3 class="mb-0 font-weight-bold">{{ $client->name }}</h3>
                                <span class="badge 
                                    @switch($loan->status)
                                        @case(0) bg-warning @break
                                        @case(1) bg-primary @break
                                        @case(2) bg-success @break
                                        @case(3) bg-danger @break
                                    @endswitch">
                                    @switch($loan->status)
                                        @case(0) {{ translate('Pending') }} @break
                                        @case(1) {{ translate('Running') }} @break
                                        @case(2) {{ translate('Paid') }} @break
                                        @case(3) {{ translate('Rejected') }} @break
                                    @endswitch
                                </span>
                                
                               
                            </div>
                            
                        </div>

                        {{-- Client and Loan Details --}}
                        <div class="row">
                            {{-- Client Details --}}
                            <div class="col-md-6">
                                <h5 class="card-title text-muted">{{ translate('Client Details') }}</h5>
                                <dl class="row ">
                                   

                                    <dt class="col-sm-4">{{ translate('Phone') }}:</dt>
                                    <dd class="col-sm-8">{{ $client->phone }}</dd>

                                    <dt class="col-sm-4">{{ translate('Address') }}:</dt>
                                    <dd class="col-sm-8">{{ $client->address }}</dd>

                                    <dt class="col-sm-4">{{ translate('Credit Balance') }}:</dt>
                                    <dd class="col-sm-8">{{ number_format($client->credit_balance, 2) }}</dd>

                                    <dt class="col-sm-4">{{ translate('Savings Balance') }}:</dt>
                                    <dd class="col-sm-8">{{ number_format($client->savings_balance, 2) }}</dd>
                                </dl>
                            </div>

                            {{-- Loan Details --}}
                            <div class="col-md-6">
                                <h5 class="card-title text-muted">{{ translate('Loan Details') }}</h5>
                                <form action="{{ route('admin.loans.saveloanedit', $loan->id) }}" method="POST">
                                    @csrf
                                    <dl class=" ">
                                       

                                         <input type="hidden" name="loan_id" value="{{ $loan->id }}">
                                            <input type="hidden" name="user_id" value="{{ $loan->user_id }}">
                                            <input type="hidden" name="client_id" value="{{ $loan->client_id }}">
                                            <input type="hidden" name="plan_id" value="{{ $loan->plan_id }}">
                                            <input type="hidden" name="trx_id" value="{{ $loan->trx }}">
                                            
                                            

                                        <dt class="col-sm-4">{{ translate('Transaction ID') }}:</dt>
                                        <dd class="col-sm-8">{{ $loan->trx }}</dd>

                                        {{-- Editable Loan Fields --}}
                                <div class="form-group mt-3">
                                    <label for="amount" class="form-label">{{ translate('Loan Amount') }}</label>
                                    <input type="number" class="form-control" id="amount" name="amount" value="{{ $loan->amount }}" required placeholder="{{ translate('Enter loan amount') }}" step="any">
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label for="per_installment" class="form-label">{{ translate('Installment') }}</label>
                                    <input type="number" class="form-control" id="per_installment" name="per_installment" value="{{ $loan->per_installment }}" required placeholder="{{ translate('Enter installment amount') }}" step="any">
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label for="installment_interval" class="form-label">{{ translate('Interval (Days)') }}</label>
                                    <input type="number" class="form-control" id="installment_interval" name="installment_interval" value="{{ $loan->installment_interval }}" required placeholder="{{ translate('Enter interval in days') }}" step="any">
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label for="processing_fee" class="form-label">{{ translate('Processing fee') }}</label>
                                    <input type="number" class="form-control" id="processing_fee" name="processing_fee" value="{{ $loan->processing_fee }}" required placeholder="{{ translate('Enter processing fee') }}" step="any">
                                </div>

                                        
                                        
                                        {{-- Save Changes Button --}}
                                        <div class="mt-4 text-end">
                                            <button type="submit" class="btn btn-primary btn-lg">{{ translate('Save Changes') }}</button>
                                        </div>
                                    </dl>
                                </form>
                            </div>
                        </div>

                        {{-- Loan Plan Details (if applicable) --}}
                        @if($loanPlan)
                        <div class="card mt-5 border-light shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">{{ translate('Loan Plan Details') }}</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row ">
                                    {{-- Add relevant loan plan details here --}}
                                </dl>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-warning" role="alert">
                    {{ translate('No loan details found.') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault();
    fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              alert('{{ translate('Loan updated successfully.') }}');
          } else {
              alert('{{ translate('Failed to update loan.') }}');
          }
      }).catch(error => {
          console.error('Error:', error);
          alert('{{ translate('An error occurred while updating the loan.') }}');
      });
});
</script>
@endpush
