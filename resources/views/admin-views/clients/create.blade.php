@extends('layouts.admin.app')

@section('title', translate('Add New Client'))

@push('css_or_js')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Dropzone CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@section('content')
<div class="content container-fluid">
    <!-- Header -->
    <div class="d-flex align-items-center mb-5">
        <img width="32" src="{{ asset('public/assets/admin/img/icons/client-add.svg') }}" alt="{{ translate('client') }}" class="me-3">
        <h1 class="page-header-title">{{ translate('Add New Client') }}</h1>
    </div>

    <!-- Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-5">
            <form action="{{ route('admin.clients.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row g-4">
                    <!-- Name Input -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Full Name') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="name" class="form-control" placeholder="{{ translate('Enter full name') }}" value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <!-- Phone Input -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Phone Number') }} <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                            <input type="tel" name="phone" class="form-control" placeholder="{{ translate('Enter phone number') }}" value="{{ old('phone') }}" required>
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Email Address') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="{{ translate('Enter email address') }}" value="{{ old('email') }}">
                        </div>
                    </div>

                    <!-- Address Input -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Residential Address') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                            <input type="text" name="address" class="form-control" placeholder="{{ translate('Enter address') }}" value="{{ old('address') }}">
                        </div>
                    </div>

                    <!-- Status Input -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Account Status') }} <span class="text-danger">*</span></label>
                        <select name="status" class="form-select select2" required>
                            <option value="">{{ translate('Select status') }}</option>
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>{{ translate('Active') }}</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                        </select>
                    </div>

                    <!-- Business Input -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Business Name') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-briefcase-fill"></i></span>
                            <input type="text" name="business" class="form-control" placeholder="{{ translate('Enter business name') }}" value="{{ old('business') }}">
                        </div>
                    </div>

                    <!-- NIN Input -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('National ID Number (NIN)') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                            <input type="text" name="nin" class="form-control" placeholder="{{ translate('Enter NIN') }}" value="{{ old('nin') }}">
                        </div>
                    </div>

                    <!-- Agent Selection -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Assigned Agent') }} <span class="text-danger">*</span></label>
                        <select name="added_by" class="form-select select2" required>
                            <option value=""> {{ translate('Select agent') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('added_by') == $user->id ? 'selected' : '' }}>
                                    {{ $user->f_name . ' ' . $user->l_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Branch Selection -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Branch Location') }} <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select select2" required>
                            <option value="">{{ translate('Select branch') }}</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->branch_id }}" {{ old('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Client Photo Input -->
                    {{-- <div class="col-lg-6">
                        <label class="form-label">{{ translate('Upload Client Photo') }}</label>
                        <div class="dropzone" id="client-photo-dropzone"></div>
                        <small class="text-muted">{{ translate('Allowed formats: jpg, png. Max size: 2MB.') }}</small>
                    </div> --}}

                    <!-- National ID Photo Input -->
                    {{-- <div class="col-lg-6">
                        <label class="form-label">{{ translate('Upload National ID Photo') }}</label>
                        <div class="dropzone" id="national-id-dropzone"></div>
                        <small class="text-muted">{{ translate('Allowed formats: jpg, png. Max size: 2MB.') }}</small>
                    </div> --}}

                    <!-- Next of Kin -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Next of Kin Name') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                            <input type="text" name="next_of_kin" class="form-control" placeholder="{{ translate('Enter next of kin name') }}" value="{{ old('next_of_kin') }}">
                        </div>
                    </div>

                    <!-- Next of Kin Phone -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Next of Kin Phone') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone-forward-fill"></i></span>
                            <input type="tel" name="next_of_kin_phone" class="form-control" placeholder="{{ translate('Enter next of kin phone') }}" value="{{ old('next_of_kin_phone') }}">
                        </div>
                    </div>

                    <!-- Next of Kin Relationship -->
                    <div class="col-lg-6">
                        <label class="form-label">{{ translate('Relationship with Next of Kin') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-heart-fill"></i></span>
                            <input type="text" name="next_of_kin_relationship" class="form-control" placeholder="{{ translate('Enter relationship') }}" value="{{ old('next_of_kin_relationship') }}">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end mt-5">
                    <button type="reset" class="btn btn-outline-secondary me-3">{{ translate('Reset') }}</button>
                    <button type="submit" class="btn btn-primary">{{ translate('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Dropzone JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "{{ translate('Please select') }}",
            width: '100%'
        });


       


        // Initialize Dropzone for Client Photo
        Dropzone.autoDiscover = false;
        var clientPhotoDropzone = new Dropzone("#client-photo-dropzone", {
            url: "{{ route('admin.clients.upload') }}",
            maxFiles: 1,
            acceptedFiles: 'image/*',
            addRemoveLinks: true,
            dictDefaultMessage: "{{ translate('Drag & drop client photo here or click') }}",
            init: function() {
                this.on("success", function(file, response) {
                    // Handle the response
                });
            }
        });

        // Initialize Dropzone for National ID Photo
        var nationalIdDropzone = new Dropzone("#national-id-dropzone", {
            url: "{{ route('admin.clients.upload') }}",
            maxFiles: 1,
            acceptedFiles: 'image/*',
            addRemoveLinks: true,
            dictDefaultMessage: "{{ translate('Drag & drop national ID photo here or click') }}",
            init: function() {
                this.on("success", function(file, response) {
                    // Handle the response
                });
            }
        });
    });
</script>
@endpush
