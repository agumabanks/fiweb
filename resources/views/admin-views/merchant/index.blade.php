@extends('layouts.admin.app')

@section('title', translate('Add New Merchant'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/store.png')}}" alt="{{ translate('merchant') }}">
            <h2 class="page-header-title">{{translate('Add New Merchant')}}</h2>
        </div>

        <div class="card card-body">
            <form action="{{route('admin.merchant.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('First Name')}}</label>
                            <input type="text" name="f_name" class="form-control" value="{{ old('f_name') }}"
                                    placeholder="{{translate('First Name')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Last Name')}}</label>
                            <input type="text" name="l_name" class="form-control" value="{{ old('l_name') }}"
                                    placeholder="{{translate('Last Name')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('email')}}
                                <small class="text-muted">({{translate('optional')}})</small></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                    placeholder="{{translate('Ex : ex@example.com')}}">
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label d-block"
                                    for="exampleFormControlInput1">{{translate('phone')}}<small class="text-danger"></small></label>
                                <div class="input-group __input-grp">
                                    <select id="country_code" name="country_code" class="__input-grp-select" required>
                                        <option value="">{{ translate('select') }}</option>
                                        @foreach(PHONE_CODE as $country_code)
                                            <option value="{{ $country_code['code'] }}" {{ $currentUserInfo && strpos($country_code['name'], $currentUserInfo->countryName) !== false ? 'selected' : '' }}>{{ $country_code['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="phone" class="form-control __input-grp-input" value="{{ old('phone') }}"
                                            placeholder="{{translate('Ex : 171*******')}}" required>
                                </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('Password')}}</label>
                        <div class="input-group input-group-merge">
                            <input type="password" name="password" class="js-toggle-password form-control form-control input-field"
                                    placeholder="{{translate('8 digit Password')}}" required minlength="8"
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
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Identification Type')}}</label>
                            <select name="identification_type" class="form-control">
                                <option value="passport">{{translate('passport')}}</option>
                                <option value="driving_license">{{translate('driving')}} {{translate('license')}}</option>
                                <option value="nid">{{translate('nid')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label"
                                    for="exampleFormControlInput1">{{translate('Identification Number')}}</label>
                            <input type="text" name="identification_number" class="form-control" value="{{ old('identification_number') }}"
                                    placeholder="{{ translate('Ex : DH-23434-LS') }}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Store Name')}}</label>
                            <input type="text" name="store_name" class="form-control" value="{{ old('store_name') }}"
                                    placeholder="{{translate('Store Name')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Address')}}</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}"
                                    placeholder="{{translate('Address')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('BIN')}}</label>
                            <input type="text" name="bin" class="form-control" value="{{ old('bin') }}"
                                    placeholder="{{translate('BIN')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('callback')}}</label>
                            <input type="text" name="callback" class="form-control" value="{{ old('callback') }}"
                                    placeholder="{{translate('callback')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group mb-3">
                            <div class="form-group mb-0">
                                <label class="input-label" for="exampleFormControlInput1">{{translate('Identification Image')}}
                                    <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small></label>

                            </div>
                        </div>
                        <div>
                            <div class="product--coba spartan_item_wrapper-area">
                                <div class="row g-2" id="coba"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label class="text-dark mb-0">{{translate('Merchant Image')}}</label>
                                <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                <label class="custom-file-label"
                                        for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                            </div>

                            <div class="text-center mt-3">
                                <img class="border rounded-10 w-200" id="viewer"
                                        src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}" alt={{ translate('merchant') }}/>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label class="text-dark mb-0">{{translate('Logo')}}</label>
                                <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="logo" id="customFileEg2" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                <label class="custom-file-label"
                                        for="customFileEg2">{{translate('choose')}} {{translate('file')}}</label>
                            </div>

                            <div class="text-center mt-3">
                                <img class="border rounded-10 w-200" id="viewer_1"
                                        src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}" alt={{ translate('merchant') }}/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>

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
        function readURL2(input) {
            if (input.files && input.files[0]) {
                var reader_1 = new FileReader();

                reader_1.onload = function (e) {
                    $('#viewer_1').attr('src', e.target.result);
                }

                reader_1.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });

        $("#customFileEg2").change(function () {
            readURL2(this);
        });

        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identification_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-4',
                maxFileSize: '',
                placeholderImage: {
                    image: '{{asset('public/assets/admin/img/400x400/img2.jpg')}}',
                    width: '100%'
                },
                dropFileLabel: "Drop Here",
                onAddRow: function (index, file) {

                },
                onRenderedPreview: function (index) {

                },
                onRemoveRow: function (index) {

                },
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
