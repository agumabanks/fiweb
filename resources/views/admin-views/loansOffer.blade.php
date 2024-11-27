@extends('layouts.admin.app')

@section('title', translate('dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header pb-2">
            <h1 class="page-header-title text-primary mb-1">{{translate('welcome')}} , {{auth('user')->user()->f_name}}.</h1>
            <p class="welcome-msg">{{ translate('Loans Plan') . ' '. Helpers::get_business_settings('business_name') . ' ' . translate('admin_panel') }}</p>
        </div>

       

        @endsection

        @push('script')
      @endpush


        @push('script_2')
            
    @endpush
