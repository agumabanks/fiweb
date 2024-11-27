@extends('layouts.admin.app')

@section('title', translate('Update Notification'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/notification.png')}}" alt="{{ translate('notification') }}">
            <h2 class="page-header-title">{{translate('update Notification')}}</h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.notification.update',[$notification['id']])}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-9">
                            <label class="input-label">{{translate('title')}}</label>
                            <input type="text" value="{{$notification['title']}}" name="title" class="form-control" placeholder="{{translate('New Notification')}}" required>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="input-label">{{translate('receiver')}}</label>
                                <select name="receiver" class="form-control js-select2-custom" id="receiver" required>
                                    <option value="all" @if($notification['receiver'] == 'all') selected @endif>{{translate('All')}}</option>
                                    <option value="customers" @if($notification['receiver'] == 'customers') selected @endif>{{translate('Customers')}}</option>
                                    <option value="agents" @if($notification['receiver'] == 'agents') selected @endif>{{translate('Agents')}}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">{{translate('description')}}</label>
                        <textarea name="description" class="form-control" rows="6" required>{{$notification['description']}}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <label class="text-dark mb-0">{{translate('Image')}}</label>
                            <small class="text-danger"> *( {{translate('ratio 3:1')}} )</small>
                        </div>

                        <div class="custom-file">
                            <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                        </div>

                        <div class="text-center mt-3">
                            <img class="border rounded-10 mx-w400 w-100" id="viewer"
                                 src="{{$notification['image_fullpath']}}" alt="{{ translate('notification') }}"/>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="submit" class="btn btn-primary">{{translate('resend')}} {{translate('notification')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script src="{{asset('public/assets/admin/js/image-upload.js')}}"></script>
@endpush

