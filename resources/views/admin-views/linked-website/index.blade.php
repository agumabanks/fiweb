@extends('layouts.admin.app')

@section('title', translate('Linked Website'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/web.png')}}" alt="{{ translate('linked_website') }}">
            <h2 class="page-header-title">{{translate('Add New Website')}}</h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.linked-website')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('name')}}</label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="{{translate('example')}}" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="input-label">{{translate('URL')}}</label>
                                <input type="text" name="url" class="form-control"
                                       placeholder="{{translate('""_www.example.com')}}" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <label class="text-dark mb-0">{{translate('Image')}}</label>
                            <small class="text-danger"> *( {{translate('ratio 1:1')}} )</small>
                        </div>
                        <div class="custom-file">
                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                            <label class="custom-file-label"
                                    for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                        </div>
                        <div class="mt-3 text-center">
                            <img class="border rounded-10 w-200" id="viewer"
                                    src="{{asset('public/assets/admin/img/400x400/img2.jpg')}}"
                                    alt="{{ translate('image') }}"/>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Linked Website Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $linkedWebsites->total() }}</span>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table id="columnSearchDatatable"
                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                        data-hs-datatables-options='{
                            "order": [],
                            "orderCellsTop": true
                        }'>
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('name')}}</th>
                        <th>{{translate('URL')}}</th>
                        <th>{{translate('image')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>

                    </thead>

                    <tbody>
                    @foreach($linkedWebsites as $key=>$linkedWebsite)
                        <tr>
                            <td>{{$linkedWebsites->firstItem()+$key}}</td>
                            <td>
                                <span class="d-block font-size-sm text-body">
                                    {{$linkedWebsite['name']}}
                                </span>
                            </td>
                            <td>{{$linkedWebsite['url']}}</td>
                            <td>
                                <img class="shadow mx-h60"
                                    src="{{$linkedWebsite['image_fullpath']}}"
                                    alt="{{ translate('image') }}">
                            </td>
                            <td>
                                <label class="switcher" for="welcome_status_{{$linkedWebsite['id']}}">
                                    <input type="checkbox" name="welcome_status"
                                            class="switcher_input change-status"
                                           data-route="{{route('admin.linked-website-status',[$linkedWebsite['id']])}}"
                                            id="welcome_status_{{$linkedWebsite['id']}}"
                                        {{$linkedWebsite?($linkedWebsite['status']==1?'checked':''):''}}>

                                    <span class="switcher_control"></span>
                                </label>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                        href="{{route('admin.linked-website-edit',[$linkedWebsite['id']])}}"><i class="tio-edit"></i></a>

                                    <a class="action-btn btn btn-outline-danger"
                                        href="{{route('admin.linked-website-delete',['id'=>$linkedWebsite['id']])}}"><i class="tio-add-to-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $linkedWebsites->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/image-upload.js')}}"></script>
@endpush
