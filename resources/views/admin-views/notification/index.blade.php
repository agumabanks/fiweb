@extends('layouts.admin.app')

@section('title', translate('Add New Notification'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/notification.png')}}" alt="{{ translate('notification') }}">
            <h2 class="page-header-title">{{translate('notification')}}</h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{route('admin.notification.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group row">
                        <div class="col-md-9">
                            <label class="input-label">{{translate('title')}}</label>
                            <input type="text" name="title" class="form-control" placeholder="{{translate('New Notification')}}" required>
                        </div>

                        <div class="col-md-3 mt-4 mt-md-0">
                            <label class="input-label">{{translate('receiver')}}</label>
                            <select name="receiver" class="form-control js-select2-custom" id="receiver" required>
                                <option value="all" selected>{{translate('All')}}</option>
                                <option value="customers">{{translate('Customers')}}</option>
                                <option value="agents">{{translate('Agents')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="input-label">{{translate('description')}}</label>
                        <textarea name="description" rows="6" class="form-control" required></textarea>
                    </div>
                    <div class="form-group">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <label class="text-dark mb-0">{{translate('Image')}}</label>
                            <small class="text-danger"> *( {{translate('ratio 3:1')}} )</small>
                        </div>

                        <div class="custom-file">
                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                            <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                        </div>
                        <div class="text-center mt-3">
                            <img class="border rounded-10 mx-w400 w-100" id="viewer"
                                 src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}" alt="{{ translate('image') }}"/>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('send')}} {{translate('notification')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Notification Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $notifications->total() }}</span>
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
                            <th>{{translate('description')}}</th>
                            <th>{{translate('receiver')}}</th>
                            <th>{{translate('image')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($notifications as $key=>$notification)
                        <tr>
                            <td>{{$notifications->firstitem()+$key}}</td>
                            <td>
                            <span class="d-block font-size-sm text-body">
                                {{substr($notification['title'],0,25)}} {{strlen($notification['title'])>25?'...':''}}
                            </span>
                            </td>
                            <td>
                                {{substr($notification['description'],0,25)}} {{strlen($notification['description'])>25?'...':''}}
                            </td>
                            <td>
                                @if($notification['receiver'] == 'all')
                                    <span class="text-uppercase badge badge-light text-muted">{{translate('all')}}</span>
                                @elseif($notification['receiver'] == 'customers')
                                    <span class="text-uppercase badge badge-light text-muted">{{translate('customers')}}</span>
                                @elseif($notification['receiver'] == 'agents')
                                    <span class="text-uppercase badge badge-light text-muted">{{translate('agents')}}</span>
                                @endif
                            </td>
                            <td>
                                @if($notification['image_fullpath']!=null)
                                    <img class="shadow rounded mx-h60"
                                            src="{{$notification['image_fullpath']}}"
                                         alt="{{ translate('notification') }}">
                                @else
                                    <label class="badge badge-light text-muted">{{translate('No image available')}}</label>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                        href="{{route('admin.notification.edit',[$notification['id']])}}"><i class="tio-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.notification.delete', $notification['id']) }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="action-btn btn btn-outline-danger">
                                            <i class="tio-add-to-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $notifications->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/image-upload.js')}}"></script>
@endpush
