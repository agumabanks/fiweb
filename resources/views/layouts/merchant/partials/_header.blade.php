<div id="headerMain" class="d-none">
    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper">
            </div>

            <div class="navbar-nav-wrap-content-left d-xl-none">
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                       data-placement="right" title="Collapse"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                       data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                       data-toggle="tooltip" data-placement="right" title="Expand"></i>
                </button>
            </div>

            <div class="navbar-nav-wrap-content-right">
                <ul class="navbar-nav align-items-center flex-row">

                    <li class="nav-item d-none d-sm-inline-block align-items-center">
                        <div class="hs-unfold">
                            @php( $local = session()->has('local')?session('local'):'en')
                            @php($lang = \App\CentralLogics\Helpers::get_business_settings('language')??null)
                            <div class="topbar-text dropdown disable-autohide text-capitalize">
                                @if(isset($lang))
                                    <a class="topbar-link dropdown-toggle d-flex align-items-center lang-country-flag text-dark"
                                       href="#" data-toggle="dropdown">
                                        @foreach($lang as $data)
                                            @if($data['code']==$local)
                                                <img
                                                    src="{{asset('public/assets/admin/img/media/google_translate_logo1.png')}}"
                                                    alt="{{translate('image')}}">
                                                <span>{{$data['name']}}</span>
                                            @endif
                                        @endforeach
                                    </a>
                                    <ul class="dropdown-menu">
                                        @foreach($lang as $key =>$data)
                                            @if($data['status']==1)
                                                <li>
                                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                                       href="{{route('admin.lang',[$data['code']])}}">
                                                        <span
                                                            class="text-capitalize">{{\App\CentralLogics\Helpers::get_language_name($data['code'])}}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </li>

                    <li class="nav-item ml-4">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper media align-items-center right-dropdown-icon"
                               href="javascript:;"
                               data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>
                                <div class="media-body pl-0 pr-2">
                                    <span class="card-title h5 text-right"> {{translate('Merchant Panel')}} </span>
                                    <span
                                        class="card-text">{{auth('user')->user()->f_name??''}} {{auth('user')->user()->l_name??''}}</span>
                                </div>
                                <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img"
                                         src="{{auth('user')->user()->image_fullpath}}"
                                         alt="Image Description">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                            </a>

                            <div id="accountNavbarDropdown"
                                 class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account min-w-16rem">
                                <div class="dropdown-item-text">
                                    <div class="media gap-3 align-items-center">
                                        <div class="avatar avatar-sm avatar-circle">
                                            <img class="avatar-img"
                                                 src="{{auth('user')->user()->image_fullpath}}"
                                                 alt="Image Description">
                                        </div>
                                        <div class="media-body">
                                            <span class="card-title h5">{{auth('user')->user()->f_name??''}}</span>
                                            <span class="card-text">{{auth('user')->user()->email??''}}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="{{ route('merchant.settings') }}">
                                    <span class="text-truncate pr-2" title="Settings">{{translate('settings')}}</span>
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item merchant-logout-btn" href="javascript:">
                                    <span class="text-truncate pr-2" title="Sign out">{{translate('sign_out')}}</span>
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>
