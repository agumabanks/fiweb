@extends('layouts.admin.app')

@section('title',translate('About us'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/info.png')}}" alt="{{ translate('media') }}">
            <h2 class="mb-0 text-capitalize">{{translate('about_us')}}</h2>
        </div>

        <div class="d-flex justify-content-end gap-3 mb-3">
            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#section-view-modal-intro">{{ translate('Section View') }} <i class="tio-document-text"></i></button>

            <button type="button" class="btn btn-outline-primary d-flex gap-2 align-items-center" data-toggle="modal" data-target="#notes-view-modal">{{ translate('Notes') }} <i class="tio-info"></i></button>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.pages.about-us')}}" method="post" enctype="multipart/form-data" id="tnc-form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="input-label" for="title">{{translate('title')}}</label>
                                <input type="text" name="about_us_title" value="{{$title->value}}" id="title" class="form-control" placeholder="{{translate('title')}}" required>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="input-label" for="sub_title">{{translate('sub_title')}}</label>
                                <input type="text" value="{{$subTitle->value}}" name="about_us_sub_title" id="sub_title" class="form-control" placeholder="{{translate('sub_title')}}" required>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <label class="text-dark mb-0">{{translate('About Us Image')}}</label>
                                    <small class="text-danger"> *( {{translate('ratio 3:1')}} )</small>
                                </div>

                                <div class="text-center mb-4">
                                    <img class="border rounded-10 mx-w300 w-100" id="viewer1"
                                    src={{Helpers::onErrorImage($image['value'], asset('storage/app/public/about-us') . '/' . $image['value'], asset('public/assets/admin/img/900x400/img1.jpg'), 'about-us/')}}
                                    alt="{{ translate('about_us') }}"/>
                                </div>

                                <div class="custom-file">
                                    <input type="file" name="about_us_image" id="customFileEg1" class="custom-file-input"
                                            accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                    <label class="custom-file-label" for="customFileEg1">{{translate('choose')}} {{translate('file')}}</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <textarea class="ckeditor form-control" name="about_us">{!! $aboutUs['value'] ?? '' !!}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

            <div class="modal fade" id="section-view-modal-intro" tabindex="-1" aria-labelledby="section-view-modalLabel-intro" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="d-flex justify-content-center">
                                <img width="100%" src="{{asset('public/assets/landing/img/section-view/about.png')}}" alt="{{ translate('about') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                            <img width="58" src="{{asset('public/assets/landing/img/media/notes.png')}}" alt="{{ translate('notes') }}">
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
                                    <h5 class="text-center">{{translate('Choose')}} <span class="bg-primary text-white">{{ Helpers::get_business_settings('business_name') }}</span> for <br>{{translate('Secure And Convenient Digital Payments')}}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection

@push('script_2')
    <script src="//cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.ckeditor').ckeditor();
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
    </script>
@endpush
