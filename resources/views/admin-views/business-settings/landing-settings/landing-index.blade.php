@extends('layouts.admin.app')

@section('title', translate("landing page settings"))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2">
            <img width="24" src="{{asset('public/assets/admin/img/media/business-setup.png')}}" alt="{{ translate('business_setup') }}">
            <h2 class="page-header-title">{{translate('Landing_Page_Setup')}}</h2>
        </div>

        <div class="inline-page-menu my-4">
            @include('admin-views.business-settings.landing-settings.partial._landing-setup-tabs')
        </div>

        @if($webPage=='intro_section')
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-intro">Section View <i class="tio-document-text"></i></button>

                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
            </div>

            <div class="card mb-5">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('header_Intro_Section')}} ({{ translate('default') }})</span>
                    </h5>
                    <div class="row">
                        <div class="col-lg-12">
                            <form method="post" id="landing-info-title-status-form">
                                @csrf
                                @method('PUT')
                                <div class="d-flex flex-wrap justify-content-between border rounded align-items-center p-2 mb-5">
                                    <h5 class="text-capitalize m-0">
                                        {{ translate('intro_section_landing_page') }}
                                        <i class="tio-info-outined" data-toggle="tooltip"
                                            title="{{ translate('You_can_turn_off/on_intro_section_of_landing_page') }}"></i>
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status toggle-switch-input"
                                            value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                                <div class="d-flex justify-content-end gap-3">
                                    <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                    <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                            class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{ translate($webPage) }}</span>
                    </h5>

                    <form method="post"
                        enctype="multipart/form-data" id="landing-info-update-form4">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-end">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="title">{{translate('title')}}</label>
                                    <input type="text" name="title" value="{{$intro['title']}}" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="sub_title">{{translate('Sub_title')}}</label>
                                    <input type="text" name="description" value="{{$intro['description']}}" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="button_name">{{translate('button_name')}}</label>
                                    <input type="text" value="{{$intro['button_name']}}" name="button_name" id="button_name" class="form-control" placeholder="{{translate('button_name')}}" >
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="download_link">{{translate('download_link')}}</label>
                                    <input type="text" value="{{$intro['download_link']}}" name="download_link" id="download_link" class="form-control" placeholder="{{translate('download_link')}}" required>
                                </div>
                            </div>
                            <input type="hidden" name="type" value="intro_section_form">
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-5">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{ translate('user_rating_and_count') }}</span>
                    </h5>

                    <form method="post" enctype="multipart/form-data" id="landing-info-update-form-rating">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="bg-light p-3 rounded h-100">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                            <label class="text-dark mb-0">{{translate('Reviewer Icon')}}</label>
                                            <small class="text-info">( {{translate('1:1')}} )</small>
                                        </div>

                                        <div class="text-center mb-4">
                                            <img class="mx-w160 w-100" id="viewer1"
                                                 src="{{$imageSource['review_user_icon']}}"
                                                 alt="{{ translate('image') }}"/>
                                        </div>

                                        <input type="hidden" name="type" value="review_and_rating">

                                        <div class="custom-file">
                                            <input type="file" name="review_user_icon" id="customFileEg1" class="custom-file-input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label" for="reviewer_name">{{translate('reviewer_name')}}</label>
                                        <input type="text" name="reviewer_name" value="{{$userRatingData['reviewer_name']}}" id="reviewer_name" class="form-control" placeholder="{{translate('reviewer_name')}}" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label" for="rating">{{translate('rating')}}</label>
                                        <input type="number" min="1" max="5" step="any" value="{{$userRatingData['rating']}}" name="rating" id="rating" class="form-control" placeholder="{{translate('rating')}}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8">
                                <div class="bg-light p-3 rounded h-100">
                                    <div class="row">
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                                    <label class="text-dark mb-0">{{translate('User 1 Image')}}</label>
                                                    <small class="text-info">( {{translate('1:1')}} )</small>
                                                </div>

                                                <div class="text-center mb-4">
                                                    <img class="mx-w160 w-100" id="middle_image_id"
                                                         src="{{$imageSource['user_image_one']}}"
                                                         alt="{{ translate('image') }}"/>
                                                </div>

                                                <div class="custom-file">
                                                    <input type="file" name="user_image_one" id="customFileEg2" class="custom-file-input"
                                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                    <label class="custom-file-label" for="customFileEg2">{{translate('choose')}} {{translate('file')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                                    <label class="text-dark mb-0">{{translate('User 2 Image')}}</label>
                                                    <small class="text-info">( {{translate('1:1')}} )</small>
                                                </div>

                                                <div class="text-center mb-4">
                                                    <img class="mx-w160 w-100" id="viewer3"
                                                         src="{{$imageSource['user_image_two']}}"
                                                         alt="{{ translate('image') }}"/>
                                                </div>

                                                <div class="custom-file">
                                                    <input type="file" name="user_image_two" id="customFileEg3" class="custom-file-input"
                                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                    <label class="custom-file-label" for="customFileEg3">{{translate('choose')}} {{translate('file')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4">
                                            <div class="form-group">
                                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                                    <label class="text-dark mb-0">{{translate('User 3 Image')}}</label>
                                                    <small class="text-info">( {{translate('1:1')}} )</small>
                                                </div>

                                                <div class="text-center mb-4">
                                                    <img class="mx-w160 w-100" id="viewer4"
                                                    src="{{$imageSource['user_image_three']}}" alt="{{ translate('image') }}"/>
                                                </div>

                                                <div class="custom-file">
                                                    <input type="file" name="user_image_three" id="customFileEg4" class="custom-file-input"
                                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                                    <label class="custom-file-label" for="customFileEg4">{{translate('choose')}} {{translate('file')}}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="input-label" for="total_user_count">{{translate('total_user_count')}}</label>
                                                <input type="number" name="total_user_count" value="{{$userRatingData['total_user_count']}}" id="total_user_count" class="form-control" placeholder="{{translate('total_user_count')}}" required>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="input-label" for="total_user_content">{{translate('total_user_content')}}</label>
                                                <input type="text" value="{{$userRatingData['total_user_content']}}" name="total_user_content" id="total_user_content" class="form-control" placeholder="{{translate('total_user_content')}}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{ translate('image_Section') }}</span>
                    </h5>

                    <form method="post"
                        enctype="multipart/form-data" id="landing-info-update-form3">
                        @csrf
                        @method('PUT')
                        <div class="d-flex flex-wrap gap-3 align-items-end">
                            <div>
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <h6 class="text-dark mb-0">{{translate('Left Image')}}</h6>
                                </div>

                                <div class="text-center mb-4">
                                    <img class="mx-w160 w-100" id="viewer6"
                                    src="{{$imageSource['intro_left_image']}}" alt="{{ translate('image') }}"/>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="intro_left_image" id="customFileEg6" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg6">{{translate('choose')}} {{translate('file')}}</label>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <h6 class="text-dark mb-0">{{translate('Middle Image')}}</h6>
                                </div>

                                <div class="text-center mb-4">
                                    <img class="mx-w160 w-100" id="viewer7"
                                    src="{{$imageSource['intro_middle_image']}}" alt="{{ translate('image') }}"/>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="intro_middle_image" id="customFileEg7" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg7">{{translate('choose')}} {{translate('file')}}</label>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <h6 class="text-dark mb-0">{{translate('Right Image')}}</h6>
                                </div>

                                <div class="text-center mb-4">
                                    <img class="mx-w160 w-100" id="viewer8"
                                    src="{{$imageSource['intro_right_image']}}"alt="{{ translate('image') }}"/>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="intro_right_image" id="customFileEg8" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg8">{{translate('choose')}} {{translate('file')}}</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <h6 class="mb-0">{{translate('Min Size for Better Resolution 300x620 px')}}</h6>
                            <small>{{translate('Image format : jpg, png, jpeg | Maximum size : 5MB')}}</small>
                        </div>

                        <input type="hidden" name="type" value="intro_section_form">

                        <div class="d-flex justify-content-end gap-3 mt-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($webPage=='feature')
            @php($feature = isset($data->value)?json_decode($data->value, true):null)
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-feature">Section View <i class="tio-document-text"></i></button>

                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
            </div>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('header_section')}} ({{ translate('default') }})</span>
                    </h5>

                    <form method="post" id="landing-info-title-status-form">
                        @csrf
                        @method('PUT')
                        <div class="row gy-3">
                            <div class="col-lg-6">
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2">
                                    <h5 class="text-capitalize m-0">
                                        {{ translate('feature_section_landing_page') }}
                                        <i class="tio-info-outined" data-toggle="tooltip"
                                            title="{{ translate('You_can_turn_off/on_feature_section_of_landing_page') }}"></i>
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status toggle-switch-input"
                                            value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="feature_title">{{translate('section_Title')}}</label>
                                    <input type="text" name="title" value="{{$title}}" id="feature_title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{translate('section_Content')}}</span>
                    </h5>

                    <form method="post"
                        enctype="multipart/form-data" id="landing-info-update-form">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-end">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="title">{{translate('title')}}</label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>
                                <div class="form-group">
                                    <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                    <input type="text" name="sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Icon / Image ')}}</label>
                                        <small class="text-info"> *( {{translate('(175 x 385 px)')}} )</small>
                                    </div>

                                    <div class="text-center mb-4">
                                        <img class="border rounded-10 mx-w160 w-100" id="viewer3"
                                        src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}"
                                             alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="image" id="customFileEg3" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg3">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-30">
                    <div class="table-responsive">
                        <table id="example" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('title')}}</th>
                                    <th>{{translate('sub_title')}}</th>
                                    <th>{{translate('image')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th>{{translate('action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($feature ??[] as $key=>$item)
                                <tr>
                                    <td>{{$item['title']}}</td>
                                    <td>{{$item['sub_title']}}</td>
                                    <td>
                                        <img class="height-50px width-50px"
                                            src="{{Helpers::onErrorImage($item['image'], asset('storage/app/public/landing-page/feature') . '/' . $item['image'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/feature/')}}"
                                            alt="{{ translate('image') }}">
                                    </td>
                                    <td>
                                        <label class="switcher" for="welcome_status_{{$key}}">
                                            <input type="checkbox" name="welcome_status"
                                                    class="switcher_input change-status"
                                                    id="welcome_status_{{$key}}" {{$item?($item['status']==1?'checked':''):''}}
                                                data-route="{{route('admin.landing-settings.landing-status-change',[$webPage,$item['id']])}}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <div class="d-flex gap-2">

                                                <button type="button" class="action-btn btn btn-outline-primary edit-feature" data-key="{{$key}}">
                                                    <i class="fa fa-pencil" aria-hidden="true"></i>
                                                </button>

                                                <button type="button"
                                                        data-id="delete-{{$item['id']}}"
                                                        data-message="{{translate('want_to_delete_this')}}?"
                                                    class="action-btn btn btn-outline-danger form-alert">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                </button>
                                                <form
                                                    action="{{route('admin.landing-settings.delete-landing-information',[$webPage,$item['id']])}}"
                                                    method="post" id="delete-{{$item['id']}}"
                                                    class="hidden">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($webPage=='screenshots')
            @php($screenshots = isset($data->value)?json_decode($data->value, true):null)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <h5 class="card-title mb-3">
                            <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('header_section')}} ({{ translate('default') }})</span>
                        </h5>
                        <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-screenshots">Section View <i class="tio-document-text"></i></button>
                    </div>

                    <form method="post" id="landing-info-title-status-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="d-flex flex-wrap justify-content-between border rounded align-items-center p-2 mb-5">
                                    <h5 class="text-capitalize m-0">
                                        {{ translate('screenshot_section_landing_page') }}
                                        <i class="tio-info-outined" data-toggle="tooltip"
                                            title="{{ translate('You_can_turn_off/on_screenshot_section_of_landing_page') }}"></i>
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status toggle-switch-input"
                                            value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{translate('image_Section')}}</span>
                    </h5>

                    <form method="post"
                        enctype="multipart/form-data" id="landing-info-update-form">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-end">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <h6 class="text-dark mb-0">{{translate('Screenshot Image')}}</h6>
                                    </div>

                                    <div class="text-center mb-2">
                                        <img class="border rounded-10 mx-w160 w-100" id="viewer1"
                                        src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}"
                                             alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="d-flex flex-column align-items-center justify-content-center mb-4">
                                        <h6 class="mb-1">{{translate('Min Size for Better Resolution 200x434 px')}}</h6>
                                        <small>{{translate('Image format : jpg, png, jpeg | Maximum size : 5MB')}}</small>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-30">
                    <div class="table-responsive">
                        <table id="example" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('image')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th>{{translate('action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($screenshots ??[] as $key=>$item)
                                <tr>
                                    <td>
                                        <img class="width-50px height-50px"
                                        src="{{Helpers::onErrorImage($item['image'], asset('storage/app/public/landing-page/screenshots') . '/' . $item['image'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/screenshots/')}}"
                                            alt="{{ translate('image') }}">
                                    </td>
                                    <td>
                                        <label class="switcher" for="welcome_status_{{$key}}">
                                            <input type="checkbox" name="welcome_status"
                                                    class="switcher_input change-status"
                                                    id="welcome_status_{{$key}}" {{$item?($item['status']==1?'checked':''):''}}
                                                    data-route="{{route('admin.landing-settings.landing-status-change',[$webPage,$item['id']])}}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="table-actions d-flex gap-2">
                                            <button type="button" class="action-btn btn btn-outline-primary edit-screenshots" data-key="{{$key}}">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </button>

                                            <button type="button"
                                                    data-id="delete-{{$item['id']}}"
                                                    data-message="{{translate('want_to_delete_this')}}?"
                                                class="action-btn btn btn-outline-danger form-alert">
                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                            </button>
                                            <form
                                                action="{{route('admin.landing-settings.delete-landing-information',[$webPage,$item['id']])}}"
                                                method="post" id="delete-{{$item['id']}}"
                                                class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($webPage=='why_choose_us')
            @php($why_choose_us = isset($data->value)?json_decode($data->value, true):null)
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-why-choose-us">Section View <i class="tio-document-text"></i></button>

                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('header_section')}} ({{ translate('default') }})</span>
                    </h5>

                    <form method="post" id="landing-info-title-status-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2">
                                    <h5 class="text-capitalize m-0">
                                        {{ translate('why_choose_us_section_landing_page') }}
                                        <i class="tio-info-outined" data-toggle="tooltip"
                                            title="{{ translate('You_can_turn_off/on_why_choose_us_section_of_landing_page') }}"></i>
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status toggle-switch-input"
                                            value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6 mb-5">
                                <div class="form-group">
                                    <label class="input-label" for="title">{{translate('section_Title')}}</label>
                                    <input type="text" name="title" value="{{$title}}" id="choose_us_title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span>
                        <span>{{translate('section_Content')}}</span>
                    </h5>

                    <form method="post"
                        enctype="multipart/form-data" id="landing-info-update-form">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-end">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="title">{{translate('title')}}</label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>

                                <div class="form-group">
                                    <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                    <input type="text" name="sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Icon / Image ')}}</label>
                                        <small class="text-info"> ( {{translate('(1:1)')}} )</small>
                                    </div>

                                    <div class="text-center mb-4">
                                        <img class="border rounded-10 mx-w160 w-100" id="viewer3"
                                        src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}"
                                             alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="image" id="customFileEg3" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg3">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-30">
                    <div class="table-responsive">
                        <table id="example" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('title')}}</th>
                                    <th>{{translate('sub_title')}}</th>
                                    <th>{{translate('image')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th>{{translate('action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($why_choose_us ??[] as $key=>$item)
                                <tr>
                                    <td>{{$item['title']}}</td>
                                    <td>{{$item['sub_title']}}</td>
                                    <td>
                                        <img class="height-50px width-50px"
                                            src="{{Helpers::onErrorImage($item['image'], asset('storage/app/public/landing-page/why-choose-us') . '/' . $item['image'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/why-choose-us/')}}"
                                            alt="{{ translate('image') }}">
                                    </td>
                                    <td>
                                        <label class="switcher" for="welcome_status_{{$key}}">
                                            <input type="checkbox" name="welcome_status"
                                                    class="switcher_input change-status"
                                                   data-route="{{route('admin.landing-settings.landing-status-change',[$webPage,$item['id']])}}"
                                                    id="welcome_status_{{$key}}" {{$item?($item['status']==1?'checked':''):''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="table-actions d-flex gap-2">
                                            <button type="button" class="action-btn btn btn-outline-primary edit-why-choose-us" data-key="{{$key}}">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </button>

                                            <button type="button"
                                                    data-id="delete-{{$item['id']}}"
                                                    data-message="{{translate('want_to_delete_this')}}?"
                                                class="action-btn btn btn-outline-danger form-alert">
                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                            </button>
                                            <form
                                                action="{{route('admin.landing-settings.delete-landing-information',[$webPage,$item['id']])}}"
                                                method="post" id="delete-{{$item['id']}}"
                                                class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($webPage=='agent_registration_section')
            @php($agent_registration = isset($data->value)?json_decode($data->value, true):null)
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-agent-registration">Section View <i class="tio-document-text"></i></button>

                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('header_section')}} ({{ translate('default') }})</span>
                    </h5>

                    <form method="post" id="landing-info-title-status-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2 mb-5">
                                    <h5 class="text-capitalize m-0">
                                        {{ translate('agent_registration_section_landing_page') }}
                                        <i class="tio-info-outined" data-toggle="tooltip"
                                            title="{{ translate('You_can_turn_off/on_agent_registration_section_of_landing_page') }}"></i>
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status toggle-switch-input"
                                            value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate($webPage) }}</span>
                        </h5>
                        <form method="post"
                            enctype="multipart/form-data" id="landing-info-update-form">
                            @csrf
                            @method('PUT')
                            <div class="row align-items-end">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="input-label" for="title">{{translate('title')}}</label>
                                        <input type="text" name="title" value="{{$agent_registration['title']}}" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Background Image')}}</label>
                                        <small class="text-info"> ( {{translate('5:1')}} )</small>
                                    </div>
                                    <div class="text-center mb-4">
                                        <img class="border rounded-10 mx-w300 w-100" id="viewer3"
                                        src="{{Helpers::onErrorImage($agent_registration['banner'], asset('storage/app/public/landing-page/agent-registration') . '/' . $agent_registration['banner'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/agent-registration/')}}"/>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <div class="custom-file">
                                            <input type="file" name="banner" id="customFileEg3" class="custom-file-input"
                                                    accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                            <label class="custom-file-label" for="customFileEg3">{{translate('choose')}} {{translate('file')}}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                        class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
        @endif

        @if($webPage=='how_it_works_section')
            @php($how_it_works = isset($data->value)?json_decode($data->value, true):null)
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-how-it-works">Section View <i class="tio-document-text"></i></button>

                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-4 ">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('section_Title')}}</span>
                    </h5>

                    <form method="post" id="landing-info-title-status-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-6">
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2">
                                    <h5 class="text-capitalize m-0">
                                        {{ translate('how_it_works_section_landing_page') }}
                                        <i class="tio-info-outined" data-toggle="tooltip"
                                            title="{{ translate('You_can_turn_off/on_how_it_works_section_of_landing_page') }}"></i>
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status toggle-switch-input"
                                            value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="title">{{translate('header_title')}}</label>
                                    <input type="text" name="title" value="{{$title}}" id="works_title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4 ">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate($webPage)}}</span>
                    </h5>
                    <form method="post"
                        enctype="multipart/form-data" id="landing-info-update-form">
                        @csrf
                        @method('PUT')
                        <div class="row align-items-end">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="title">{{translate('title')}}</label>
                                    <input type="text" name="title" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>

                                <div class="form-group">
                                    <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                    <input type="text" name="sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Image')}}</label>
                                        <small class="text-info"> ( {{translate('1:1')}} )</small>
                                    </div>

                                    <div class="text-center mb-4">
                                        <img class="border rounded-10 mx-w300 w-100" id="viewer3"
                                        src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}"
                                             alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="image" id="customFileEg3" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg3">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-30">
                    <div class="table-responsive">
                        <table id="example" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{translate('title')}}</th>
                                    <th>{{translate('sub_title')}}</th>
                                    <th>{{translate('image')}}</th>
                                    <th>{{translate('status')}}</th>
                                    <th>{{translate('action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($how_it_works ??[] as $key=>$item)
                                <tr>
                                    <td>{{$item['title']}}</td>
                                    <td>{{$item['sub_title']}}</td>
                                    <td>
                                        <img class="height-50px width-50px"
                                        src="{{Helpers::onErrorImage($item['image'], asset('storage/app/public/landing-page/how-it-works') . '/' . $item['image'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/how-it-works/')}}"
                                            alt="{{ translate('image') }}">
                                    </td>
                                    <td>
                                        <label class="switcher" for="welcome_status_{{$key}}">
                                            <input type="checkbox" name="welcome_status"
                                                    class="switcher_input change-status"
                                                    id="welcome_status_{{$key}}" {{$item?($item['status']==1?'checked':''):''}}
                                                    data-route="{{route('admin.landing-settings.landing-status-change',[$webPage,$item['id']])}}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="table-actions d-flex gap-2">
                                            <button type="button" class="action-btn btn btn-outline-primary edit-how-it-works" data-key="{{$key}}">
                                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                            </button>

                                            <button type="button"
                                                    data-id="delete-{{$item['id']}}"
                                                    data-message="{{translate('want_to_delete_this')}}?"
                                                class="action-btn btn btn-outline-danger form-alert">
                                                <i class="fa fa-trash-o" aria-hidden="true"></i>
                                            </button>
                                            <form
                                                action="{{route('admin.landing-settings.delete-landing-information',[$webPage,$item['id']])}}"
                                                method="post" id="delete-{{$item['id']}}"
                                                class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($webPage=='download_section')
            @php($download = isset($data->value)?json_decode($data->value, true):null)
            <div class="d-flex justify-content-end gap-3 mb-3">
                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-download">Section View <i class="tio-document-text"></i></button>

                <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('header_section')}} ({{ translate('default') }})</span>
                    </h5>

                    <form method="post" id="landing-info-title-status-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2 mb-5">
                                    <h5 class="text-capitalize m-0">
                                        {{ translate('download_section_landing_page') }}
                                        <i class="tio-info-outined" data-toggle="tooltip"
                                            title="{{ translate('You_can_turn_off/on_download_section_of_landing_page') }}"></i>
                                    </h5>
                                    <label class="toggle-switch toggle-switch-sm">
                                        <input type="checkbox" class="status toggle-switch-input"
                                            value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                        <span class="toggle-switch-label text mb-0">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate($webPage) }}</span>
                    </h5>

                    <form method="post"
                        enctype="multipart/form-data" id="landing-info-update-form">
                        @csrf
                        @method('PUT')
                        <div class="row justify-content-center align-items-end">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="title">{{translate('title')}}</label>
                                    <input type="text" name="title" value="{{$download['title']}}" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                    <input type="text" value="{{$download['sub_title']}}" name="sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="play_store_link">{{translate('play_Store_Link')}}</label>
                                    <input type="text" value="{{$download['play_store_link']}}" name="play_store_link" id="play_store_link" class="form-control" placeholder="{{translate('play_store_link')}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="input-label" for="app_store_link">{{translate('app_Store_Link')}}</label>
                                    <input type="text" value="{{$download['app_store_link']}}" name="app_store_link" id="app_store_link" class="form-control" placeholder="{{translate('app_store_link')}}">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Image')}}</label>
                                        <small class="text-info">( {{translate('3:1')}} )</small>
                                    </div>

                                    <div class="text-center mb-4">
                                        <img class="border rounded-10 mx-w300 w-100" id="viewer1"
                                        src="{{Helpers::onErrorImage($download['image'], asset('storage/app/public/landing-page/download-section') . '/' . $download['image'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/download-section/')}}"
                                             alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="image" id="customFileEg1" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                    class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($webPage=='business_statistics')
        @php($business_download = isset($businessStatsDownloadData->value)?json_decode($businessStatsDownloadData->value, true):null)
        <div class="d-flex justify-content-end gap-3 mb-3">
            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-business-statistics">Section View <i class="tio-document-text"></i></button>

            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{translate('header_section')}} ({{ translate('default') }})</span>
                </h5>

                <form method="post" id="landing-info-title-status-form">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="maintainance-mode-toggle-bar d-flex flex-wrap justify-content-between border rounded align-items-center p-2 mb-5">
                                <h5 class="text-capitalize m-0">
                                    {{ translate('business_statistics_landing_page') }}
                                    <i class="tio-info-outined" data-toggle="tooltip"
                                        title="{{ translate('You_can_turn_off/on_business_statistics_of_landing_page') }}"></i>
                                </h5>
                                <label class="toggle-switch toggle-switch-sm">
                                    <input type="checkbox" class="status toggle-switch-input"
                                        value="1" name="status" {{$status == '1' ? 'checked' : ''}}>
                                    <span class="toggle-switch-label text mb-0">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-5">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate('Business Download') }}</span>
                </h5>

                <form method="post"
                    enctype="multipart/form-data" id="landing-info-update-form">
                    @csrf
                    @method('PUT')
                    <div class="row gy-3 align-items-end">
                        <div class="col-lg-6">
                            <div>
                                <label class="input-label" for="title">{{translate('title')}}</label>
                                <input type="text" name="title" value="{{$business_download['title']}}" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div>
                                <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                <input type="text" value="{{$business_download['sub_title']}}" name="sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <h5 class="card-title mb-3">
                                <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate('Download App') }}</span>
                            </h5>

                            <div class="bg-light rounded p-3">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Download Icon')}}</label>
                                        <small class="text-info">( {{translate('1:1')}} )</small>
                                    </div>

                                    <div class="text-center mb-4">
                                        <img class="mx-w160 w-100" id="viewer1"
                                        src="{{Helpers::onErrorImage($business_download['download_icon'], asset('storage/app/public/landing-page/business-statistics') . '/' . $business_download['download_icon'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/business-statistics/')}}"
                                        alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="download_icon" id="customFileEg1" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="input-label" for="download_count">{{translate('download_count')}}</label>
                                    <input type="number" name="download_count" value="{{$business_download['download_count']}}" id="download_count" class="form-control" placeholder="{{translate('download_count')}}" required>
                                    <input type="hidden" name="type" value="business_statistics_download">
                                </div>

                                <div>
                                    <label class="input-label" for="download_description">{{translate('download_description')}}</label>
                                    <textarea class="form-control" name="download_description" id="download_description" rows="4" placeholder="{{translate('download_sort_description')}}">{{$business_download['download_sort_description']}}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <h5 class="card-title mb-3">
                                <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate('Total Ratings') }}</span>
                            </h5>

                            <div class="bg-light rounded p-3">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Review Icon')}}</label>
                                        <small class="text-info">( {{translate('1:1')}} )</small>
                                    </div>

                                    <div class="text-center mb-4">
                                        <img class="mx-w160 w-100" id="viewer5"
                                        src="{{Helpers::onErrorImage($business_download['review_icon'], asset('storage/app/public/landing-page/business-statistics') . '/' . $business_download['review_icon'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/business-statistics/')}}"
                                       alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="review_icon" id="customFileEg5" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg5">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="input-label" for="review_count">{{translate('review_count')}}</label>
                                    <input type="number" name="review_count" value="{{$business_download['review_count']}}" id="review_count" class="form-control" placeholder="{{translate('review_count')}}" required>
                                </div>
                                <div>
                                    <label class="input-label" for="review_description">{{translate('review_description')}}</label>
                                    <textarea class="form-control" name="review_description" id="review_description" rows="4" placeholder="{{translate('review_sort_description')}}">{{$business_download['review_sort_description']}}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <h5 class="card-title mb-3">
                                <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate('Total Count') }}</span>
                            </h5>

                            <div class="bg-light rounded p-3">
                                <div class="form-group">
                                    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                        <label class="text-dark mb-0">{{translate('Country Icon')}}</label>
                                        <small class="text-info">( {{translate('1:1')}} )</small>
                                    </div>

                                    <div class="text-center mb-4">
                                        <img class="mx-w160 w-100" id="viewer4"
                                        src="{{Helpers::onErrorImage($business_download['country_icon'], asset('storage/app/public/landing-page/business-statistics') . '/' . $business_download['country_icon'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/business-statistics/')}}"
                                        alt="{{ translate('image') }}"/>
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="country_icon" id="customFileEg4" class="custom-file-input"
                                                accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                        <label class="custom-file-label" for="customFileEg4">{{translate('choose')}} {{translate('file')}}</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="input-label" for="country_count">{{translate('country_count')}}</label>
                                    <input type="number" name="country_count" value="{{$business_download['country_count']}}" id="country_count" class="form-control" placeholder="{{translate('country_count')}}" required>
                                </div>
                                <div>
                                    <label class="input-label" for="country_description">{{translate('country_description')}}</label>
                                    <textarea class="form-control" name="country_description" id="country_description" rows="4" placeholder="{{translate('country_sort_description')}}">{{$business_download['country_sort_description']}}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            @php($testimonial = isset($testimonialData->value)?json_decode($testimonialData->value, true):null)
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate('testimonial') }}</span>
                </h5>

                <form method="post"
                    enctype="multipart/form-data" id="landing-info-update-form2">
                    @csrf
                    @method('PUT')
                    <div class="row justify-content-center align-items-end">
                        <div class="col-lg-8 col-xxl-9">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="input-label" for="name">{{translate('Reviewer Name')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control" placeholder="{{translate('name')}}" required>
                                        <input type="hidden" name="type" value="testimonial">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="input-label" for="rating">{{translate('rating')}}</label>
                                        <select name="rating" id="rating" class="form-control js-select2-custom" required>
                                            <option value="0.5">{{translate('0.5')}}</option>
                                            <option value="1">{{translate('1')}}</option>
                                            <option value="1.5">{{translate('1.5')}}</option>
                                            <option value="2">{{translate('2')}}</option>
                                            <option value="2.5">{{translate('2.5')}}</option>
                                            <option value="3">{{translate('3')}}</option>
                                            <option value="3.5">{{translate('3.5')}}</option>
                                            <option value="4">{{translate('4')}}</option>
                                            <option value="4.5">{{translate('4.5')}}</option>
                                            <option value="5">{{translate('5')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="input-label" for="user_type">{{translate('user_type')}}</label>
                                        <input type="text" name="user_type" id="user_type" class="form-control" placeholder="{{translate('user_type')}}" required>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="opinion">{{translate('Review')}} <span class="text-danger">*</span></label>
                                        <textarea name="opinion" id="opinion" class="form-control" rows="4" placeholder="{{translate('Ex: Very Good Company')}}" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-xxl-3">
                            <div class="form-group">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <label class="text-dark mb-0">{{translate('Reviewer Image')}}</label>
                                    <small class="text-info">( {{translate('1:1')}} )</small>
                                </div>

                                <div class="text-center mb-4">
                                    <img class="border rounded-10 mx-w300 w-100 contain-twoByOne" id="viewer3"
                                    src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}"
                                         alt="{{ translate('image') }}"/>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="image" id="customFileEg3" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg3">{{translate('choose')}} {{translate('file')}}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                    </div>
                </form>
            </div>

            <div class="card-body p-30">
                <div class="table-responsive">
                    <table id="example" class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th>{{translate('Reviewer Image')}}</th>
                                <th>{{translate('Reviewer Name')}}</th>
                                <th>{{translate('opinion')}}</th>
                                <th>{{translate('rating')}}</th>
                                <th>{{translate('User_Type')}}</th>
                                <th>{{translate('status')}}</th>
                                <th>{{translate('action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($testimonial ??[] as $key=>$item)
                            <tr>
                                <td>
                                    <img class="width-50px height-50px"
                                        src="{{Helpers::onErrorImage($item['image'], asset('storage/app/public/landing-page/testimonial') . '/' . $item['image'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/testimonial/')}}"
                                        alt="{{ translate('image') }}">
                                </td>
                                <td>{{$item['name']}}</td>
                                <td>{{$item['opinion']}}</td>
                                <td>{{$item['rating']}}</td>
                                <td>{{$item['user_type']}}</td>
                                <td>
                                    <label class="switcher" for="welcome_status_{{$key}}">
                                        <input type="checkbox" name="welcome_status"
                                                class="switcher_input change-status"
                                                data-route="{{route('admin.landing-settings.landing-status-change',[$webPage,$item['id']])}}"
                                                id="welcome_status_{{$key}}" {{$item?($item['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="table-actions d-flex gap-2">
                                        <button type="button" class="action-btn btn btn-outline-primary edit-testimonial" data-key="{{$key}}">
                                            <i class="fa fa-pencil" aria-hidden="true"></i>
                                        </button>

                                        <button type="button"
                                                data-id="delete-{{$item['id']}}"
                                                data-message="{{translate('want_to_delete_this')}}?"
                                            class="action-btn btn btn-outline-danger form-alert">
                                            <i class="fa fa-trash-o" aria-hidden="true"></i>
                                        </button>
                                        <form action="{{route('admin.landing-settings.delete-landing-information',[$webPage,$item['id']])}}"
                                            method="post" id="delete-{{$item['id']}}"
                                            class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        @if($webPage=='contact_us_section')
        @php($contact_us = isset($data->value)?json_decode($data->value, true):null)
        <div class="d-flex justify-content-end gap-3 mb-3">
            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-contact">Section View <i class="tio-document-text"></i></button>

            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">Notes <i class="tio-info"></i></button>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <span class="card-header-icon mr-2"><i class="tio-calendar"></i></span> <span>{{ translate($webPage) }}</span>
                </h5>

                <form method="post" enctype="multipart/form-data" id="landing-info-update-form">
                    @csrf
                    @method('PUT')
                    <div class="row align-items-end">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="input-label" for="title">{{translate('title')}}</label>
                                <input type="text" name="title" value="{{$contact_us['title']}}" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                            </div>
                        </div>
                        <div class="col-lg-6 ">
                            <div class="form-group">
                                <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                <input type="text" value="{{$contact_us['sub_title']}}" name="sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    </div>

    <!-- Section View Modal -->
    <div class="modal fade" id="section-view-modal-intro" tabindex="-1" aria-labelledby="section-view-modalLabel-intro" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" src="{{asset('public/assets/landing/img/section-view/intro.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-feature" tabindex="-1" aria-labelledby="section-view-modalLabel-feature" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" src="{{asset('public/assets/landing/img/section-view/feature.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-screenshots" tabindex="-1" aria-labelledby="section-view-modalLabel-screenshots" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" src="{{asset('public/assets/landing/img/section-view/screenshots.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-why-choose-us" tabindex="-1" aria-labelledby="section-view-modalLabel-why-choose-us" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" src="{{asset('public/assets/landing/img/section-view/choose-us.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-agent-registration" tabindex="-1" aria-labelledby="section-view-modalLabel-agent-registration" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" height="100%" src="{{asset('public/assets/landing/img/section-view/agent-reg.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-how-it-works" tabindex="-1" aria-labelledby="section-view-modalLabel-how-it-works" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" height="100%" src="{{asset('public/assets/landing/img/section-view/Frame.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-download" tabindex="-1" aria-labelledby="section-view-modalLabel-download" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" height="100%" src="{{asset('public/assets/landing/img/section-view/download.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-business-statistics" tabindex="-1" aria-labelledby="section-view-modalLabel-business-statistics" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" height="100%" src="{{asset('public/assets/landing/img/section-view/business-statistics.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="section-view-modal-contact" tabindex="-1" aria-labelledby="section-view-modalLabel-contact" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center">
                        <img width="100%" height="100%" src="{{asset('public/assets/landing/img/section-view/contact.png')}}" alt="{{ translate('image') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes View Modal -->
    <div class="modal fade" id="notes-view-modal" tabindex="-1" aria-labelledby="notes-view-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center mb-4">
                        <img width="58" src="{{asset('public/assets/landing/img/media/notes.png')}}" alt="{{ translate('image') }}">
                    </div>

                    <h5 class="mb-3">{{translate('For Title and Headline')}} </h5>

                    <ul class="list-unstyled d-flex flex-column gap-2">
                        <li>{{translate('1. To include a text color just use  ** around the text **  you want to use colour')}}</li>
                        <li>{{translate('2. To include a text background just use  ## around the text ##  you want to use background colour')}}</li>
                        <li>{{translate('3. To include a text bold just use  @@ around the text @@  you want to use bold')}}</li>
                        <li>{{translate('4. If you want to break the line just use  %%  from where you want to break')}}</li>
                    </ul>

                    <div class="d-flex justify-content-center mt-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-center">{{translate('Choose')}} <span class="bg-primary text-white">{{Helpers::get_business_settings('business_name')}}</span> for <br>{{translate('Secure And Convenient Digital Payments')}}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
<script>
    "use strict";

    $('#landing-info-update-form').on('submit', function (event) {
        event.preventDefault();

        var form = $('#landing-info-update-form')[0];
        var formData = new FormData(form);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('admin.landing-settings.set-landing-information')}}?web_page={{$webPage}}",
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (response) {
                if (response.errors.length > 0) {
                    response.errors.forEach((value, key) => {
                        toastr.error(value.message);
                    });
                } else {
                    toastr.success('{{translate('successfully_updated')}}');
                    setTimeout(() => {
                        location.reload()
                    }, 1000);
                }
            },
            error: function (jqXHR, exception) {
                toastr.error(jqXHR.responseJSON.message);
            }
        });
    });

    $('#landing-info-update-form2').on('submit', function (event) {
        event.preventDefault();

        var form = $('#landing-info-update-form2')[0];
        var formData = new FormData(form);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('admin.landing-settings.set-landing-information')}}?web_page={{$webPage}}",
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (response) {
                if (response.errors.length > 0) {
                    response.errors.forEach((value, key) => {
                        toastr.error(value.message);
                    });
                } else {
                    toastr.success('{{translate('successfully_updated')}}');
                    setTimeout(() => {
                        location.reload()
                    }, 1000);
                }
            },
            error: function (jqXHR, exception) {
                toastr.error(jqXHR.responseJSON.message);
            }
        });
    });

    $('#landing-info-update-form3').on('submit', function (event) {
        event.preventDefault();

        var form = $('#landing-info-update-form3')[0];
        var formData = new FormData(form);
        // Set header if need any otherwise remove setup part
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('admin.landing-settings.set-landing-information')}}?web_page={{$webPage}}",
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (response) {
                if (response.errors.length > 0) {
                    response.errors.forEach((value, key) => {
                        toastr.error(value.message);
                    });
                } else {
                    toastr.success('{{translate('successfully_updated')}}');
                    setTimeout(() => {
                        location.reload()
                    }, 1000);
                }
            },
            error: function (jqXHR, exception) {
                toastr.error(jqXHR.responseJSON.message);
            }
        });
    });

    $('#landing-info-update-form4').on('submit', function (event) {
        event.preventDefault();

        var form = $('#landing-info-update-form4')[0];
        var formData = new FormData(form);

        // Set header if need any otherwise remove setup part
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('admin.landing-settings.set-landing-information')}}?web_page={{$webPage}}",
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (response) {
                if (response.errors.length > 0) {
                    response.errors.forEach((value, key) => {
                        toastr.error(value.message);
                    });
                } else {
                    toastr.success('{{translate('successfully_updated')}}');
                    setTimeout(() => {
                        location.reload()
                    }, 1000);
                }
            },
            error: function (jqXHR, exception) {
                toastr.error(jqXHR.responseJSON.message);
            }
        });
    });


    $('#landing-info-update-form-rating').on('submit', function (event) {
        event.preventDefault();

        var form = $('#landing-info-update-form-rating')[0];
        var formData = new FormData(form);
        // Set header if need any otherwise remove setup part
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('admin.landing-settings.set-landing-information')}}?web_page={{$webPage}}",
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (response) {
                if (response.errors.length > 0) {
                    response.errors.forEach((value, key) => {
                        toastr.error(value.message);
                    });
                } else {
                    toastr.success('{{translate('successfully_updated')}}');
                    setTimeout(() => {
                        location.reload()
                    }, 1000);
                }
            },
            error: function (jqXHR, exception) {
                toastr.error(jqXHR.responseJSON.message);
            }
        });
    });

    $('#landing-info-title-status-form').on('submit', function (event) {
        event.preventDefault();

        var form = $('#landing-info-title-status-form')[0];
        var formData = new FormData(form);
        // Set header if need any otherwise remove setup part
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url: "{{route('admin.landing-settings.set-landing-title-status')}}?web_page={{$webPage}}",
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (response) {
                if (response.errors.length > 0) {
                    response.errors.forEach((value, key) => {
                        toastr.error(value.message);
                    });
                } else {
                    toastr.success('{{translate('successfully_updated')}}');
                    setTimeout(() => {
                        location.reload()
                    }, 1000);
                }
            },
            error: function (jqXHR, exception) {
                toastr.error(jqXHR.responseJSON.message);
            }
        });
    });

    function readURL(input, view_id) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#' + view_id).attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

        $("#customFileEg1").change(function () {
            readURL(this,'viewer1');
        });

        $("#customFileEg2").change(function () {
            readURL(this,'middle_image_id');
        });

        $("#customFileEg3").change(function () {
            readURL(this,'viewer3');
        });

        $("#customFileEg4").change(function () {
            readURL(this,'viewer4');
        });

        $("#customFileEg5").change(function () {
            readURL(this,'viewer5');
        });

        $("#customFileEg6").change(function () {
            readURL(this,'viewer6');
        });

        $("#customFileEg7").change(function () {
            readURL(this,'viewer7');
        });

        $("#customFileEg8").change(function () {
            readURL(this,'viewer8');
        });
</script>
<script>
    $(document).ready(function () {
        //feature
        $(".edit-feature").on("click", function () {
            var key = $(this).data("key");

            var featureData = @json($feature ?? []);

            var selectedFeature = featureData[key];

            if (selectedFeature) {
                $("#title").val(selectedFeature.title);
                $("#sub_title").val(selectedFeature.sub_title);

                $("#viewer3").attr("src", "{{ asset('storage/app/public/landing-page/feature') }}/" + selectedFeature.image);

                if ($("#id").length === 0) {
                    $("<input>")
                        .attr("type", "hidden")
                        .attr("id", "id")
                        .attr("name", "id")
                        .appendTo("#landing-info-update-form");
                }

                $("#id").val(selectedFeature.id);

                $("html, body").animate({
                    scrollTop: $("#landing-info-update-form").offset().top
                }, 500);
            }
        });

        //screenshots
        $(".edit-screenshots").on("click", function () {
            var key = $(this).data("key");
            var screenshotData = @json($screenshots ?? []);

            var selectedScreenshot = screenshotData[key];

            if (selectedScreenshot) {
                $("#viewer1").attr("src", "{{ asset('storage/app/public/landing-page/screenshots') }}/" + selectedScreenshot.image);

                if ($("#id").length === 0) {
                    $("<input>")
                        .attr("type", "hidden")
                        .attr("id", "id")
                        .attr("name", "id")
                        .appendTo("#landing-info-update-form");
                }

                $("#id").val(selectedScreenshot.id);

                $("html, body").animate({
                    scrollTop: $("#landing-info-update-form").offset().top
                }, 500);
            }
        });

        //why choose us
        $(".edit-why-choose-us").on("click", function () {
        var key = $(this).data("key");
        var chooseUsData = @json($why_choose_us ?? []);

        var selectedChooseUs = chooseUsData[key];

        if (selectedChooseUs) {

            $("#title").val(selectedChooseUs.title);
            $("#sub_title").val(selectedChooseUs.sub_title);

            $("#viewer3").attr("src", "{{ asset('storage/app/public/landing-page/why-choose-us') }}/" + selectedChooseUs.image);

            if ($("#id").length === 0) {
                $("<input>")
                    .attr("type", "hidden")
                    .attr("id", "id")
                    .attr("name", "id")
                    .appendTo("#landing-info-update-form");
            }

            $("#id").val(selectedChooseUs.id);

            $("html, body").animate({
                scrollTop: $("#landing-info-update-form").offset().top
            }, 500);
        }
        });

        //How it works
        $(".edit-how-it-works").on("click", function () {
        var key = $(this).data("key");
        var worksData = @json($how_it_works ?? []);

        var selectedWorks = worksData[key];

        if (selectedWorks) {

            $("#title").val(selectedWorks.title);
            $("#sub_title").val(selectedWorks.sub_title);

            $("#viewer3").attr("src", "{{ asset('storage/app/public/landing-page/how-it-works') }}/" + selectedWorks.image);

            if ($("#id").length === 0) {
                $("<input>")
                    .attr("type", "hidden")
                    .attr("id", "id")
                    .attr("name", "id")
                    .appendTo("#landing-info-update-form");
            }

            $("#id").val(selectedWorks.id);

            $("html, body").animate({
                scrollTop: $("#landing-info-update-form").offset().top
            }, 500);
        }
        });

        //Testimonial
        $(".edit-testimonial").on("click", function () {
        var key = $(this).data("key");
        var worksData = @json($testimonial ?? []);

        var selectedTestimonial = worksData[key];

        if (selectedTestimonial) {

            $("#rating").val(selectedTestimonial.rating);
            $('#rating').val(selectedTestimonial.rating).trigger('change');
            $("#name").val(selectedTestimonial.name);
            $("#opinion").val(selectedTestimonial.opinion);
            $("#user_type").val(selectedTestimonial.user_type);

            $("#viewer3").attr("src", "{{ asset('storage/app/public/landing-page/testimonial') }}/" + selectedTestimonial.image);

            if ($("#id").length === 0) {
                $("<input>")
                    .attr("type", "hidden")
                    .attr("id", "id")
                    .attr("name", "id")
                    .appendTo("#landing-info-update-form2");
            }

            $("#id").val(selectedTestimonial.id);

            $("html, body").animate({
                scrollTop: $("#landing-info-update-form2").offset().top
            }, 500);
        }
        });


    });

</script>

@endpush
