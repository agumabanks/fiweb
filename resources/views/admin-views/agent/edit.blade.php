@extends('layouts.admin.app')

@section('title', translate('Update Agent'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/agent.png')}}" alt="{{ translate('agent') }}">
            <h2 class="page-header-title">{{translate('Update_Agent')}}</h2>
        </div>

        <div class="card card-body">
            <form action="{{route('admin.agent.update',[$agent['id']])}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('First Name')}}</label>
                            <input type="text" name="f_name" class="form-control" value="{{ $agent['f_name']??'' }}"
                                    placeholder="{{translate('First Name')}}" required>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Last Name')}}</label>
                            <input type="text" name="l_name" class="form-control" value="{{ $agent['l_name']??'' }}"
                                    placeholder="{{translate('Last Name')}}" required>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('email')}}
                                <small>({{translate('optional')}})</small></label>
                            <input type="email" name="email" class="form-control" value="{{ $agent['email']??'' }}"
                                    placeholder="{{translate('Ex : ex@example.com')}}">
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('phone')}}</label>
                            <span class="form-control" data-toggle="tooltip" data-placement="top"
                             data-original-title="{{translate('Not Editable')}}"
                             for="exampleFormControlInput1">{{ $agent['phone']??'' }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Gender')}}</label>
                            <select name="gender" class="form-control">
                                <option value="" selected disabled>{{translate('Select Gender')}}</option>
                                <option value="male" {{ $agent['gender'] == 'male' ? 'selected' : '' }}>{{translate('Male')}}</option>
                                <option value="female" {{ $agent['gender'] == 'female' ? 'selected' : '' }}>{{translate('Female')}}</option>
                                <option value="other" {{ $agent['gender'] == 'other' ? 'selected' : '' }}>{{translate('Other')}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Occupation')}}</label>
                            <input type="text" name="occupation" class="form-control" value="{{ $agent['occupation']??'' }}"
                                    placeholder="{{translate('Ex : Businessman')}}" required>
                        </div>
                    </div>

                    <div class="col-sm-6 col-lg-4 mb-5">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('PIN')}}</label>
                        <div class="input-group input-group-merge">
                            <input type="password" name="password" class="js-toggle-password form-control form-control input-field"
                                    placeholder="{{translate('4 digit PIN')}}" maxlength="4"
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

                <div class="form-group mt-4 mt-md-0">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <label class="text-dark mb-0">{{translate('Agent Image')}}</label>
                        <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small>
                    </div>

                    <div class="custom-file">
                        <input type="file"  accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" name="image" id="file"  onchange="loadFile(event)" class="d-none">
                        <label class="custom-file-label cursor-pointer" for="file">{{translate('choose file')}}</label>
                    </div>

                    <div class="text-center mt-3">
                        <img class="border rounded-10 w-200" id="viewer"
                            src="{{$agent['image_fullpath']}}" alt="{{ translate('agent image') }}"/>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end">
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

        let loadFile = function(event) {
            let image = document.getElementById('viewer');
            image.src = URL.createObjectURL(event.target.files[0]);
        };

        $(function () {
            $("#coba").spartanMultiImagePicker({
                fieldName: 'identity_image[]',
                maxCount: 5,
                rowHeight: '120px',
                groupClassName: 'col-2',
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
