@extends('layouts.admin.app')

@section('title', translate('Upload App'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex align-items-center gap-3 mb-3">
        <img width="24" src="{{ asset('public/assets/admin/img/media/app-upload.png') }}" alt="{{ translate('Upload App') }}">
        <h2 class="page-header-title">{{ translate('Upload App') }}</h2>
    </div>

    <!-- Display Validation Errors and Flash Messages -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Upload App Form -->
    <div class="card card-body">
        <form action="{{ route('admin.apps.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <!-- App Name -->
            <div class="mb-3">
                <label for="appName" class="form-label">{{ translate('App Name') }}</label>
                <input type="text" class="form-control" id="appName" name="name" placeholder="{{ translate('Enter app name') }}" value="{{ old('name') }}" required>
            </div>

            <!-- App Description -->
            <div class="mb-3">
                <label for="appDescription" class="form-label">{{ translate('Description') }}</label>
                <textarea class="form-control" id="appDescription" name="description" rows="3" placeholder="{{ translate('Enter app description') }}">{{ old('description') }}</textarea>
            </div>

            <!-- App Version -->
            <div class="mb-3">
                <label for="appVersion" class="form-label">{{ translate('Version') }}</label>
                <input type="text" class="form-control" id="appVersion" name="version" placeholder="{{ translate('Enter app version (e.g., 1.0.0)') }}" value="{{ old('version') }}">
            </div>

            <!-- App File -->
            <div class="mb-3">
                <label for="appFile" class="form-label">{{ translate('App File') }}</label>
                <input type="file" class="form-control" id="appFile" name="file" accept=".apk, .ipa, .zip, .exe" required>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-3 justify-content-end">
                <button type="reset" class="btn btn-secondary">{{ translate('Reset') }}</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">{{ translate('Upload') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('script')
<script>
    // Disable the submit button to prevent multiple submissions
    document.getElementById('uploadForm').addEventListener('submit', function() {
        document.getElementById('submitBtn').disabled = true;
    });
</script>
@endpush
