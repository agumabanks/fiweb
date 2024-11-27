@extends('layouts.admin.app')

@section('title', translate('Linked Website'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/web.png')}}" alt="{{ translate('linked_website') }}">
            <h2 class="page-header-title">{{translate('Update Website')}}</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.linked-website')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('name')}}</label>
                                <input type="text" name="name" class="form-control" value="{{ $linkedWebsite['name'] }}"
                                       placeholder="{{translate('example')}}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('URL')}}</label>
                                <input type="text" name="url" class="form-control" value="{{ $linkedWebsite['url'] }}"
                                       placeholder="{{translate('""_www.example.com')}}" required>
                            </div>
                        </div>

                        <div class="form-group col-12">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label class="text-dark mb-0">{{translate('Image')}}</label>
                                <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small>
                            </div>

                            <div class="custom-file">
                                <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                       accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" >
                                <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                            </div>
                            <div class="mt-4 text-center">
                                <img class="border rounded-10 w-200" id="viewer"
                                     src="{{$linkedWebsite['image_fullpath']}}"
                                     alt="{{ translate('linked_website') }}"/>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id" class="form-control" value="{{ $linkedWebsite['id'] }}">

                    <div class="d-flex justify-content-end gap-3">
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
