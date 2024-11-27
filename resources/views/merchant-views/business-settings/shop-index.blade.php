@extends('layouts.merchant.app')

@section('title', translate('Integration Settings'))

@section('content')
    <div class="content container-fluid">

        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/store.png')}}" alt="{{translate('image')}}">
            <h1 class="page-header-title">{{translate('shop')}} {{translate('settings')}}</h1>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('merchant.business-settings.shop-settings-update')}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('Store Name')}}</label>
                                <input type="text" name="store_name" class="form-control"
                                       value="{{ $merchant->store_name }}"
                                       placeholder="{{translate('Store Name')}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('Address')}}</label>
                                <input type="text" name="address" class="form-control" value="{{ $merchant->address }}"
                                       placeholder="{{translate('Address')}}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('BIN')}}</label>
                                <input type="text" name="bin" class="form-control" value="{{ $merchant->bin }}"
                                       placeholder="{{translate('BIN')}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('callback')}}</label>
                                <input type="text" name="callback" class="form-control"
                                       value="{{ $merchant->callback }}"
                                       placeholder="{{translate('callback')}}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <label class="text-dark mb-0">{{translate('Logo')}}</label>
                            <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small>
                        </div>

                        <div class="custom-file">
                            <input type="file" name="logo" id="customFileEg1" class="custom-file-input d-none"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label"
                                   for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                        </div>

                        <div class="text-center mt-3">
                            <img class="border rounded-10 w-200" id="viewer2"
                                 src="{{$merchant['logo_fullpath']}}"
                                 alt="merchant image"/>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict"

        const loadFileLogo = function (event) {
            var image2 = document.getElementById('viewer2');
            image2.src = URL.createObjectURL(event.target.files[0]);
        };

        $('.custom-file-input').on('change', function() {
            loadFileLogo(event);
        });

    </script>

    <script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>

@endpush
