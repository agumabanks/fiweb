@extends('layouts.admin.app')

@section('title', translate('Add New Agent'))

@section('content')
<div class="content container-fluid">
    <div class="d-flex align-items-center gap-3 mb-3">
        <img width="24" src="{{ asset('public/assets/admin/img/media/agent.png') }}" alt="{{ translate('agent') }}">
        <h2 class="page-header-title">{{ translate('Add New Agent') }}</h2>
    </div>

    <div class="card card-body">
        <form action="{{ route('admin.agent.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{ translate('First Name') }}</label>
                        <input type="text" name="f_name" class="form-control" value="{{ old('f_name') }}"
                            placeholder="{{ translate('First Name') }}" required>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{ translate('Last Name') }}</label>
                        <input type="text" name="l_name" class="form-control" value="{{ old('l_name') }}"
                            placeholder="{{ translate('Last Name') }}" required>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{ translate('email') }}
                            <small class="text-muted">({{ translate('optional') }})</small></label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                            placeholder="{{ translate('Ex : ex@example.com') }}">
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="input-label d-block">{{ translate('phone') }}<small class="text-danger"></small></label>
                        <div class="input-group __input-grp">
                            <select id="country_code" name="country_code" class="__input-grp-select" required>
                                <option value="" disabled selected>{{ translate('select') }}</option>
                                @foreach(PHONE_CODE as $country_code)
                                <option value="{{ $country_code['code'] }}" {{ $currentUserInfo && strpos($country_code['name'], $currentUserInfo->countryName) !== false ? 'selected' : '' }}>
                                    {{ $country_code['name'] }}
                                </option>
                                @endforeach
                            </select>
                            <input type="number" name="phone" class="form-control __input-grp-input" value="{{ old('phone') }}"
                                placeholder="{{ translate('Ex : 171*******') }}" required>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="input-label">{{ translate('Gender') }}</label>
                        <select name="gender" class="form-control" required>
                            <option value="" selected disabled>{{ translate('Select Gender') }}</option>
                            <option value="male" {{ (old("gender") == 'male' ? "selected":"") }}>{{ translate('Male') }}</option>
                            <option value="female" {{ (old("gender") == 'female' ? "selected":"") }}>{{ translate('Female') }}</option>
                            <option value="other" {{ (old("gender") == 'other' ? "selected":"") }}>{{ translate('Other') }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="form-group">
                        <label class="input-label">{{ translate('Occupation') }}</label>
                        <input type="text" name="occupation" class="form-control" value="{{ old('occupation') }}"
                            placeholder="{{ translate('Ex : Businessman') }}" required>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <label class="input-label">{{ translate('PIN') }}</label>
                    <div class="input-group input-group-merge">
                        <input type="password" name="password" class="js-toggle-password form-control form-control input-field"
                            placeholder="{{ translate('4 digit PIN') }}" required maxlength="4"
                            data-hs-toggle-password-options='{
                                "target": "#changePassTarget",
                                "defaultClass": "tio-hidden-outlined",
                                "showClass": "tio-visible-outlined",
                                "classChangeTarget": "#changePassIcon"
                            }'>
                        <div id="changePassTarget" class="input-group-append">
                            <a class="input-group-text" href="javascript:">
                                <i id="changePassIcon" class="tio-visible-outlined"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group pt-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <label class="text-dark mb-0">{{ translate('Agent Image') }}</label>
                    <small class="text-danger"> *( {{ translate('ratio 1:1') }} )</small>
                </div>
                <div class="custom-file">
                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                    <label class="custom-file-label" for="customFileEg1">{{ translate('choose') }} {{ translate('file') }}</label>
                </div>

                <div class="text-center mt-3">
                    <img class="border rounded-10 w-200" id="viewer"
                        src="{{ asset('public/assets/admin/img/400x400/img2.jpg') }}" alt="{{ translate('agent image') }}" />
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end">
                <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
                <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
            </div>
        </form>
    </div>

    <!-- Button to trigger upload modal -->
    <button type="button" class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#uploadAppModal">
        {{ translate('Upload App') }}
    </button>

    <!-- Upload App Modal -->
    <div class="modal fade" id="uploadAppModal" tabindex="-1" aria-labelledby="uploadAppModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.apps.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadAppModalLabel">{{ translate('Upload App') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="appName" class="form-label">{{ translate('App Name') }}</label>
                            <input type="text" class="form-control" id="appName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="appDescription" class="form-label">{{ translate('Description') }}</label>
                            <textarea class="form-control" id="appDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="appVersion" class="form-label">{{ translate('Version') }}</label>
                            <input type="text" class="form-control" id="appVersion" name="version">
                        </div>
                        <div class="mb-3">
                            <label for="appFile" class="form-label">{{ translate('App File') }}</label>
                            <input type="file" class="form-control" id="appFile" name="file" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ translate('Upload App') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script_2')
<script>
    "use strict";

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#viewer').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#customFileEg1").change(function () {
        readURL(this);
    });
</script>
@endpush
