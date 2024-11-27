@extends('layouts.admin.app')

@section('title', translate('Update bonus'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/banner.png')}}" alt="{{ translate('bonus') }}">
            <h2 class="page-header-title">{{translate('update bonus')}}</h2>
        </div>

        <div class="card card-body mb-3">
            <form action="{{route('admin.bonus.update', [$bonus->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row align-items-end">
                    <div class="col-lg-6 form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('title')}}</label>
                        <input type="text" name="title" class="form-control" placeholder="{{translate('title')}}" value="{{$bonus->title}}" required>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('User Type')}}</label>
                        <select name="user_type" class="form-control js-select2-custom" required>
                            <option value="all" {{$bonus->user_type == 'all' ? 'selected' : ''}}>{{translate('All')}}</option>
                            <option value="customer" {{$bonus->user_type == 'customer' ? 'selected' : ''}}>{{translate('Customer')}}</option>
                            <option value="agent" {{$bonus->user_type == 'agent' ? 'selected' : ''}}>{{translate('Agent')}}</option>
                        </select>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('Minimum Add Money Amount')}}</label>
                        <input type="number" min="0" step="any" name="maximum_add_money_amount" class="form-control" value="{{$bonus->min_add_money_amount}}" required>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('limit Per User')}}
                            <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                               title="{{ translate('Each user will only receive the bonus up to the limit') }}"></i>
                        </label>
                        <input type="number" name="limit_per_user" min="1" class="form-control" value="{{$bonus->limit_per_user}}" required>
                    </div>

                    <div class="col-lg-4 form-group">
                        <label class="input-label">{{translate('Bonus Type')}}</label>
                        <select name="bonus_type" id="user_type" class="form-control js-select2-custom" required>
                            <option value="flat" {{$bonus->bonus_type == 'flat' ? 'selected' : ''}}>{{translate('Flat')}}</option>
                            <option value="percentage" {{$bonus->bonus_type == 'percentage' ? 'selected' : ''}}>{{translate('Percentage')}}</option>
                        </select>
                    </div>

                    <div class="col-lg-4 form-group">
                        <label class="input-label">{{translate('bonus')}} <span id="bonus_label__span">({{\App\CentralLogics\helpers::currency_symbol()}})</span></label>
                        <input type="number" min="0" step="any" name="bonus" class="form-control" value="{{$bonus->bonus}}" required>
                    </div>

                    <div class="col-lg-4 form-group {{$bonus->bonus_type == 'flat' ? 'd-none' : ''}}" id="maximum_bonus_amount__div">
                        <label class="input-label">{{translate('maximum Bonus Amount')}}</label>
                        <input type="number" step="any" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" value="{{$bonus->max_bonus_amount}}" {{$bonus->bonus_type == 'flat' ? '' : 'required'}}>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('Start Date')}}</label>
                        <input type="datetime-local" name="start_date_time" class="form-control" value="{{$bonus->start_date_time}}" required>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('End Date')}}</label>
                        <input type="datetime-local" name="end_date_time" class="form-control" value="{{$bonus->end_date_time}}" required>
                    </div>
                </div>
                <div class="d-flex gap-3 justify-content-end">
                    <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Update bonus')}}</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        $("#user_type").change( function() {
            var val = $("#user_type option:selected").val();
            if(val === 'percentage') {
                $("#bonus_label__span").html('(%)');
                $("#maximum_bonus_amount__div").removeClass('d-none');
                $("#maximum_bonus_amount").prop('required',true);

            } else if(val === 'flat') {
                $("#bonus_label__span").html('({{\App\CentralLogics\helpers::currency_symbol()}})');
                $("#maximum_bonus_amount__div").addClass('d-none');
                $("#maximum_bonus_amount").prop('required',false);
            }
        });
    </script>
@endpush
