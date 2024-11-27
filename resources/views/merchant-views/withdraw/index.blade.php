@extends('layouts.merchant.app')

@section('title', translate('Withdraw Request'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="22" src="{{asset('public/assets/admin/img/media/cash-withdrawal.png')}}" alt="{{translate('image')}}">
            <h1 class="page-header-title">{{translate('Withdraw')}}</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('merchant.withdraw.request-store') }}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">{{translate('withdraw methods')}}</label>
                                <select name="withdrawal_method_id" class="form-control js-select2-custom"
                                        id="withdrawal_method_id" required>
                                    <option value="" selected>{{translate('select')}}</option>
                                    @foreach($withdrawalMethods as $method)
                                        <option value="{{ $method->id }}">{{ $method->method_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <label class="input-label">{{translate('amount')}}</label>
                                    <span>{{translate('Available Balance')}} : {{ Helpers::set_symbol($maximumAmount) }}</span>
                                </div>
                                <input type="number" name="amount" min="0.1" max="{{ $maximumAmount }}" step="any"
                                       class="form-control" placeholder="{{translate('amount')}}" required>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="form-group">
                                <label class="input-label">{{translate('Sender Note')}}</label>
                                <input type="text" name="sender_note" class="form-control"
                                       placeholder="{{translate('Sender Note')}}">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="payment_method_div"></div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('Add Request')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>

        "use strict"

        $(document).ready(function () {

            $('#withdrawal_method_id').change(function () {
                let withdrawal_method_id = this.value;
                $('#payment_method_div').empty();
                $.ajax({
                    url: '{{route('merchant.withdraw.method-data')}}',
                    dataType: 'json',
                    type: 'GET',
                    data: {
                        withdrawal_method_id: withdrawal_method_id,
                    },

                    error: function () {
                        toastr.error('Server error', Error, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                    },
                    success: function (data) {
                        for (let i = 0; i < data.method_fields.length; i++) {
                            let name = data.method_fields[i].input_name;
                            let label_name = name.replace("_", " ");

                            $('#payment_method_div').append(`
                                <div class="form-group col-12  col-md-6">
                                    <label class="input-label text-capitalize" for="exampleFormControlInput1">${label_name}</label>
                                    <input type="${data.method_fields[i].input_type}" name="${data.method_fields[i].input_name}" class="form-control" placeholder="${data.method_fields[i].placeholder ?? ''}" required>
                                </div>
                            `)
                        }
                    },
                });
            });

        });


    </script>
@endpush
