@extends('layouts.admin.app')

@section('title', translate('Add New Client'))

@section('content')
    <div class="content container-fluid">
        <!-- Header with a minimalist approach -->
        <div class="d-flex align-items-center mb-4">
            <img width="24" src="{{ asset('public/assets/admin/img/media/rating.png') }}" alt="{{ translate('client') }}" class="me-2">
            <h1 class="page-header-title">{{ translate('Add New Client') }}</h1>
        </div>

        <!-- Card with sleek design -->
        <div class="card shadow-sm border-0 rounded">
            <div class="card-body">
                <form action="{{ route('admin.clients.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <!-- Name Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Name') }}</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>

                        <!-- Phone Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                        </div>

                        <!-- Address Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Address') }}</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                        </div>

                        <!-- Status Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Status') }}</label>
                            <input type="text" name="status" class="form-control" value="{{ old('status') }}" required>
                        </div>

                        <!-- KYC Verified At Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('KYC Verified At') }}</label>
                            <input type="date" name="kyc_verified_at" class="form-control" value="{{ old('kyc_verified_at') }}">
                        </div>

                        <!-- DOB Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Date of Birth') }}</label>
                            <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
                        </div>

                        <!-- Business Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Business') }}</label>
                            <input type="text" name="business" class="form-control" value="{{ old('business') }}">
                        </div>

                        <!-- NIN Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('NIN') }}</label>
                            <input type="text" name="nin" class="form-control" value="{{ old('nin') }}">
                        </div>

                        <!-- Credit Balance Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Credit Balance') }}</label>
                            <input type="number" name="credit_balance" class="form-control" value="{{ old('credit_balance') }}" required>
                        </div>

                        <!-- Savings Balance Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Savings Balance') }}</label>
                            <input type="number" name="savings_balance" class="form-control" value="{{ old('savings_balance') }}" required>
                        </div>

                        <!-- Agent Selection -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Added By') }}</label>
                            <select name="added_by" class="form-select" required>
                                <option value="">{{ translate('Select Agent') }}</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('added_by') == $user->id ? 'selected' : '' }}>
                                        {{ $user->f_name . ' ' . $user->l_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch Selection -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Branch') }}</label>
                            <select name="branch_id" class="form-select" required>
                                <option value="">{{ translate('Select Branch') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->branch_id }}" {{ old('branch_id') == $branch->branch_id ? 'selected' : '' }}>
                                        {{ $branch->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Client Photo Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Client Photo') }}</label>
                            <input type="file" name="client_photo" class="form-control" accept="image/*">
                        </div>

                        <!-- Next of Kin -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Next of Kin') }}</label>
                            <input type="text" name="next_of_kin" class="form-control" value="{{ old('next_of_kin') }}">
                        </div>

                        <!-- Next of Kin Phone -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Next of Kin Phone') }}</label>
                            <input type="text" name="next_of_kin_phone" class="form-control" value="{{ old('next_of_kin_phone') }}">
                        </div>

                        <!-- Next of Kin Relationship -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('Next of Kin Relationship') }}</label>
                            <input type="text" name="next_of_kin_relationship" class="form-control" value="{{ old('next_of_kin_relationship') }}">
                        </div>

                        <!-- National ID Photo Input -->
                        <div class="col-lg-4">
                            <label class="form-label">{{ translate('National ID Photo') }}</label>
                            <input type="file" name="national_id_photo" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end mt-4 gap-3">
                        <button type="reset" class="btn btn-light">{{ translate('Reset') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script src="{{ asset('public/assets/admin/js/image-upload.js') }}"></script>
    <script src="{{ asset('public/assets/admin/js/spartan-multi-image-picker.js') }}"></script>
    <script type="text/javascript">
        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-2',
                placeholderImage: {
                    image: '{{ asset('public/assets/admin/img/400x400/img2.jpg') }}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) { },
                onRenderedPreview: function (index) { },
                onRemoveRow: function (index) { },
                onExtensionErr: function (index, file) {
                    toastr.error('Please only input png or jpg type file', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                },
                onSizeErr: function (index, file) {
                    toastr.error('File size too big', {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            });
        });
    </script>
@endpush
