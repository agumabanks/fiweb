@extends('layouts.admin.app')

@section('title', translate('Add New Banner'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/banner.png')}}" alt="{{ translate('banner') }}">
            <h2 class="page-header-title">{{translate('Add New Banner')}}</h2>
        </div>

        <div class="card card-body mb-3">
            <form action="{{route('admin.banner.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('title')}}</label>
                            <input type="text" name="title" class="form-control" placeholder="{{translate('title')}}" required>
                        </div>
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('URL')}}</label>
                            <input type="text" name="url" class="form-control" placeholder="{{translate('URL')}}" required>
                        </div>
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('receiver')}}</label>
                            <select name="receiver" class="form-control js-select2-custom" id="receiver" required>
                                <option value="all" selected>{{translate('All')}}</option>
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
                                        src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}" alt="{{ translate('image') }}"/>
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
                    <button type="submit" class="btn btn-primary">{{translate('Add Banner')}}</button>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header __wrap-gap-10 flex-between">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Banner Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $banners->total() }}</span>
                </div>
                <div>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                    class="form-control mn-md-w280"
                                    placeholder="{{translate('Search by Title')}}" aria-label="Search"
                                    value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('title')}}</th>
                        <th>{{translate('URL')}}</th>
                        <th>{{translate('image')}}</th>
                        <th>{{translate('status')}}</th>
                        <th>{{translate('receiver')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($banners as $key=>$banner)
                        <tr>
                            <td>{{$banners->firstitem()+$key}}</td>
                            <td>
                                {{substr($banner['title'],0,25)}} {{strlen($banner['title'])>25?'...':''}}
                            </td>
                            <td>
                                <a class="text-dark" href="{{ $banner['url'] }}">{{substr($banner['url'],0,25)}} {{strlen($banner['url'])>25?'...':''}}</a>
                            </td>
                            <td>
                                @if($banner['image']!=null)
                                    <img class="shadow mx-h80"
                                            src="{{$banner['image_fullpath']}}"
                                            alt="{{ translate('banner-image') }}">
                                @else
                                    <label class="badge badge-soft-warning">{{translate('No Image')}}</label>
                                @endif
                            </td>
                            <td>
                                <label class="switcher" for="welcome_status_{{$banner['id']}}">
                                    <input type="checkbox" name="welcome_status"
                                            class="switcher_input change-status"
                                            data-route="{{route('admin.banner.status',[$banner['id']])}}"
                                            id="welcome_status_{{$banner['id']}}" {{$banner?($banner['status']==1?'checked':''):''}}>

                                    <span class="switcher_control"></span>
                                </label>
                            </td>
                            <td class="">
                                @if(isset($banner['receiver']))
                                    <span class="text-muted">{{ translate($banner['receiver'] ?? '') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                        href="{{route('admin.banner.edit',[$banner['id']])}}">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                    <a class="action-btn btn btn-outline-danger"
                                        href="{{route('admin.banner.delete',[$banner['id']])}}">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $banners->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/image-upload.js')}}"></script>
@endpush
