@extends('layouts.merchant.app')

@section('title','Profile Settings')

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">{{translate('Settings')}}</h1>
                </div>

                <div class="col-sm-auto">
                    <a class="btn btn-primary" href="{{route('merchant.dashboard')}}">
                        <i class="tio-home mr-1"></i> {{translate('Dashboard')}}
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="navbar-vertical navbar-expand-lg mb-3 mb-lg-5">
                    <button type="button" class="navbar-toggler btn btn-block btn-white mb-3"
                            aria-label="Toggle navigation" aria-expanded="false" aria-controls="navbarVerticalNavMenu"
                            data-toggle="collapse" data-target="#navbarVerticalNavMenu">
                <span class="d-flex justify-content-between align-items-center">
                  <span class="h5 mb-0">{{translate('Nav menu')}}</span>

                  <span class="navbar-toggle-default">
                    <i class="tio-menu-hamburger"></i>
                  </span>

                  <span class="navbar-toggle-toggled">
                    <i class="tio-clear"></i>
                  </span>
                </span>
                    </button>

                    <div id="navbarVerticalNavMenu" class="collapse navbar-collapse">
                        <ul id="navbarSettings"
                            class="js-sticky-block js-scrollspy navbar-nav navbar-nav-lg nav-tabs card card-navbar-nav">
                            <li class="nav-item ">
                                <a class="nav-link active text-primary" href="javascript:" id="generalSection">
                                    <i class="tio-user-outlined nav-icon"></i> Basic information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-primary" href="javascript:" id="passwordSection">
                                    <i class="tio-lock-outlined nav-icon"></i> {{translate('Password')}}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <form action="{{env('APP_MODE')!='demo'?route('merchant.settings'):'javascript:'}}" method="post"
                      enctype="multipart/form-data" id="admin-settings-form">
                    @csrf
                    <div class="card mb-3 mb-lg-5" id="generalDiv">
                        <div class="profile-cover">
                            <div class="profile-cover-img-wrapper"></div>
                        </div>

                        <label
                            class="avatar avatar-xxl avatar-circle avatar-border-lg avatar-uploader profile-cover-avatar"
                            for="avatarUploader">
                            <img id="viewer" class="avatar-img w-100"
                                 src="{{auth('user')->user()->image_fullpath}}"
                                 alt="Image">

                            <input type="file" name="image" class="js-file-attach avatar-uploader-input"
                                   id="customFileEg1"
                                   accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                            <label class="avatar-uploader-trigger" for="customFileEg1">
                                <i class="tio-edit avatar-uploader-icon shadow-soft"></i>
                            </label>
                        </label>
                    </div>

                    <div class="card mb-3 mb-lg-5">
                        <div class="card-header">
                            <h2 class="card-title h4"><i class="tio-info"></i> {{translate('Basic information')}}</h2>
                        </div>

                        <div class="card-body">
                            <div class="row form-group">
                                <label for="firstNameLabel" class="col-sm-3 col-form-label input-label">{{translate('Full name')}} <i
                                        class="tio-help-outlined text-body ml-1" data-toggle="tooltip"
                                        data-placement="top"
                                        title="Display name"></i></label>

                                <div class="col-sm-9">
                                    <div class="input-group input-group-sm-down-break">
                                        <input type="text" class="form-control" name="f_name" id="firstNameLabel"
                                               placeholder="Your first name" aria-label="Your first name"
                                               value="{{auth('user')->user()->f_name}}">
                                        <input type="text" class="form-control" name="l_name" id="lastNameLabel"
                                               placeholder="Your last name" aria-label="Your last name"
                                               value="{{auth('user')->user()->l_name}}">
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <label for="newEmailLabel" class="col-sm-3 col-form-label input-label">{{translate('Email')}} <span
                                        class="input-label-secondary">({{translate('Optional')}})</span></label>

                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="email" id="newEmailLabel"
                                           value="{{auth('user')->user()->email}}"
                                           placeholder="Enter new email address" aria-label="Enter new email address">
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button"
                                        class="btn btn-primary basic-info">{{translate('Save changes')}}</button>
                            </div>

                        </div>
                    </div>
                </form>

                <div id="passwordDiv" class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-title">{{translate('Change your password')}}</h4>
                    </div>

                    <div class="card-body">
                        <form id="changePasswordForm"
                              action="{{env('APP_MODE')!='demo'?route('merchant.settings-password'):'javascript:'}}"
                              method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row form-group">
                                <label for="newPassword"
                                       class="col-sm-3 col-form-label input-label">{{translate('New password')}}</label>

                                <div class="col-sm-9 input-group input-group-merge">
                                    <input type="password" name="password"
                                           class="js-toggle-password form-control form-control input-field"
                                           id="password"
                                           placeholder="{{translate('Enter new password')}}" required
                                           data-hs-toggle-password-options='{
                                        "target": "#changePassTarget",
                                        "defaultClass": "tio-hidden-outlined",
                                        "showClass": "tio-visible-outlined",
                                        "classChangeTarget": "#changePassIcon"
                                        }'>
                                    <div id="changePassTarget" class="input-group-append">
                                        <a class="input-group-text" href="javascript:">
                                            <i id="changePassIcon" class="tio-visible-outlined mr-3"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="row form-group">
                                <label for="confirmNewPasswordLabel"
                                       class="col-sm-3 col-form-label input-label">{{translate('Confirm password')}}</label>

                                <div class="col-sm-9 input-group input-group-merge mb-3">
                                    <input type="password" name="confirm_password"
                                           class="js-toggle-password form-control form-control input-field"
                                           id="confirm_password"
                                           placeholder="{{translate('Confirm your new password')}}" required
                                           data-hs-toggle-password-options='{
                                                "target": "#changeConPassTarget",
                                                "defaultClass": "tio-hidden-outlined",
                                                "showClass": "tio-visible-outlined",
                                                "classChangeTarget": "#changeConPassIcon"
                                                }'>
                                    <div id="changeConPassTarget" class="input-group-append">
                                        <a class="input-group-text" href="javascript:">
                                            <i id="changeConPassIcon" class="tio-visible-outlined mr-3"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary password-info">{{translate('Save Changes')}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="stickyBlockEndPoint"></div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $('.basic-info').on('click', function () {
            var appMode = "{{ env('APP_MODE') }}";

            if (appMode !== 'demo') {
                form_alert('admin-settings-form', 'Want to update merchant info ?');
            } else {
                call_demo();
            }
        });

        $('.password-info').on('click', function () {
            var appMode = "{{ env('APP_MODE') }}";

            if (appMode !== 'demo') {
                form_alert('changePasswordForm', 'Want to update merchant password ?')
            } else {
                call_demo();
            }
        });

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });

        $("#generalSection").click(function () {
            $("#passwordSection").removeClass("active");
            $("#generalSection").addClass("active");
            $('html, body').animate({
                scrollTop: $("#generalDiv").offset().top
            }, 2000);
        });

        $("#passwordSection").click(function () {
            $("#generalSection").removeClass("active");
            $("#passwordSection").addClass("active");
            $('html, body').animate({
                scrollTop: $("#passwordDiv").offset().top
            }, 2000);
        });

    </script>

@endpush
