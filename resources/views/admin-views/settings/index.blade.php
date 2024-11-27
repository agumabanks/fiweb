

<!--@push('script')-->
    <!-- Include any additional scripts here -->
    
<!--    @extends('layouts.admin.app')-->

<!--@section('title', $pageTitle)-->

<!--@push('css_or_js')-->
    <!-- Additional CSS if needed -->
<!--    <style>-->
<!--        .savings-table th, .savings-table td {-->
<!--            vertical-align: middle;-->
<!--        }-->
<!--    </style>-->
<!--@endpush-->

<!--@section('content')-->
<!--<div class="container-fluid content">-->

    
<!--</div>-->
<!--@endsection-->

<!--@push('script')-->
<!--@endpush-->

@extends('layouts.admin.app')

@section('content')
<div class="container">
    <h1>Settings</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="default_period">Default Report Period</label>
            <select name="default_period" id="default_period" class="form-control">
                <option value="daily" {{ $settings->where('key', 'default_period')->first()?->value == 'daily' ? 'selected' : '' }}>Daily</option>
                <option value="weekly" {{ $settings->where('key', 'default_period')->first()?->value == 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="monthly" {{ $settings->where('key', 'default_period')->first()?->value == 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="custom" {{ $settings->where('key', 'default_period')->first()?->value == 'custom' ? 'selected' : '' }}>Custom</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>
@endsection
