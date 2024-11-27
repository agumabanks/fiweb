@extends('layouts.admin.app')

@section('title', translate('Edit Title'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/target.png')}}" alt="{{ translate('purpose') }}">
            <h2 class="page-header-title">{{translate('Update Purpose')}}</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.purpose.update', ['id'=>$purpose->id])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group lang_form">
                                <label class="input-label"
                                       for="exampleFormControlInput1">{{ translate('name') }}</label>
                                <input type="text" name="title" class="form-control" value="{{ $purpose->title??'' }}"
                                       placeholder="{{translate('New Title')}}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group lang_form">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <label class="text-dark mb-0">{{translate('Color')}}</label>
                                    <small class="text-danger"> * ( {{ translate('choose_in_HEXA_format') }} )</small>
                                </div>
                                <input type="color" name="color" class="form-control p-1 overflow-hidden cursor-pointer" value="{{ $purpose->color??'' }}"
                                       placeholder="{{translate('Hexa color code')}}" required>
                            </div>
                        </div>
                        <div class="col-12 from_part_2">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label class="text-dark mb-0">{{translate('Image')}}</label>
                                <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                       accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                <label class="custom-file-label" for="customFileEg1">
                                    {{ translate('choose file') }}</label>
                            </div>
                        </div>
                        <div class="col-12 from_part_2">
                            <div class="form-group">
                                <div class="text-center mt-3">
                                    <img class="border rounded-10 mx-w300 w-100" id="viewer"
                                         src="{{$purpose->logo_fullpath}}"
                                         alt="{{ translate('image') }}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="id" class="form-control" value="{{ $purpose->id }}">
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('Reset')}}</button>
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
