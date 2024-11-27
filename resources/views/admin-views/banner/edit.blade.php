@extends('layouts.admin.app')

@section('title', translate('Update Banner'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/banner.png')}}" alt="{{ translate('banner') }}">
            <h2 class="page-header-title">{{translate('Update Banner')}}</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.banner.update',[$banner['id']])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('title')}}</label>
                                <input type="text" name="title" class="form-control" placeholder="{{translate('title')}}" value="{{$banner['title']}}" required>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{translate('URL')}}</label>
                                <input type="text" name="url" class="form-control" placeholder="{{translate('URL')}}" value="{{$banner['url']}}"  required>
                            </div>
                            <div class="form-group">
                                <label class="input-label">{{translate('receiver')}}</label>
                                <select name="receiver" class="form-control js-select2-custom" id="receiver">
                                    <option value="" selected disabled>{{translate('Update receiver')}}</option>
                                    <option value="all">{{translate('All')}}</option>
                                    <option value="customers">{{translate('Customers')}}</option>
                                    <option value="agents">{{translate('Agents')}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <label class="text-dark mb-0">{{translate('banner Image')}}</label>
                                    <small class="text-danger"> *( {{translate('ratio 3:1')}} )</small>
                                </div>

                                <div class="text-center mb-4">
                                    <img class="border rounded-10 mx-w300 w-100" id="viewer"
                                        src="{{$banner['image_fullpath']}}"
                                        alt="{{ translate('banner') }}"/>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-3 justify-content-end">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/image-upload.js')}}"></script>
@endpush
