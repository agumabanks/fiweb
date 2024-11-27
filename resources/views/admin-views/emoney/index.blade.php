@extends('layouts.admin.app')

@section('title', translate('EMoney'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/dollar-2.png')}}" alt="{{ translate('emoney') }}">
            <h1 class="page-header-title">{{translate('E-Money')}}</h1>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-1">
                    <h5 class="card-title d-flex align-items-center gap-2">
                        <img src="{{asset('public/assets/admin/img/media/business-analytics.png')}}" class="card-icon" alt="{{ translate('analytics') }}">
                        {{translate('E-Money Statistics')}}
                    </h5>
                </div>

                <div class="row g-2" id="order_stats">
                    @include('admin-views.emoney.partials._stats', ['data'=>$balance])
                </div>
            </div>
        </div>

        <div class="card card-body">
            <form action="{{route('admin.emoney.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="input-label"
                            for="exampleFormControlInput1">{{translate('Generate EMoney')}}</label>
                    <input type="number" id="amount" name="amount" step=".01" class="form-control" min="1"
                            value="{{ old('number') }}" placeholder="{{ translate('EX: 100') }}">
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">{{translate('Generate')}}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
