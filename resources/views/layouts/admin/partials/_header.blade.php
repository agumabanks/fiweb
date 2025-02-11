<div id="headerMain" class="d-none ">
    <header id="header"
            class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered bg-primary">
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

                    <!-- <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">-->

                    <!--    <a class="topbar-link  d-flex align-items-center lang-country-flag text-dark" href="{{route('admin.cache.clear')}}" >-->
                    <!--                    Refresh-->
                    <!--    </a>-->
                    <!--</li>-->


                    <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5 ">
                        <a class="topbar-link d-flex align-items-center lang-country-flag text-white" href="{{route('admin.cache.clear')}}" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <path id="_74846e5be5db5b666d3893933be03656" data-name="74846e5be5db5b666d3893933be03656" d="M7.719,8.911H8.9V10.1H7.719v1.185H6.539V10.1H5.36V8.911h1.18V7.726h1.18ZM5.36,13.652h1.18v1.185H5.36v1.185H4.18V14.837H3V13.652H4.18V12.467H5.36Zm13.626-2.763H10.138V10.3a1.182,1.182,0,0,1,1.18-1.185h2.36V2h1.77V9.111h2.36a1.182,1.182,0,0,1,1.18,1.185ZM18.4,18H16.044a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,14.755,18H12.5a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,11.216,18H8.958a22.825,22.825,0,0,0,1.163-5.926H18.99A19.124,19.124,0,0,1,18.4,18Z" transform="translate(-3 -2)" fill="#717580"/>
                        </svg>                        </a>
                    </li>

                    <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">

                        <a class="topbar-link  d-flex align-items-center lang-country-flag text-white" href="{{route('admin.allclients')}}" >
                                        All Clients
                        </a>
                    </li>

                       {{-- Teller  --}}
                       <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">

                        <a class="topbar-link  d-flex align-items-center lang-country-flag text-white" href="{{route('admin.teller.index')}}" >
                            Teller Pay
                        </a>
                    </li>




                    <li class="nav-item d-none d-sm-inline-flex align-items-center mr-5">
                        <div class="hs-unfold">
                            @php( $local = session()->has('local')?session('local'):'en')
                            @php($lang = \App\CentralLogics\Helpers::get_business_settings('language')??null)
                            <div class="topbar-text dropdown disable-autohide text-capitalize">
                                @if(isset($lang))
                                    <a class="topbar-link dropdown-toggle d-flex align-items-center lang-country-flag text-white" href="#" data-toggle="dropdown">
                                         Staff Section
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/agent/add')?'active':''}}">
                                            <a class="nav-link " href="{{route('admin.customer.list')}}"
                                               title="{{translate('add')}}">

                                                <span class="text-truncate">{{translate('All Staff')}}</span>
                                            </a>
                                        </li>
                                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/agent/list')?'active':''}}">
                                            <a class="nav-link " href="{{route('admin.agent.list')}}"
                                               title="{{translate('list')}}">

                                                <span class="text-truncate">{{translate('Role')}}</span>
                                            </a>
                                        </li>

                                        <li class="nav-item {{Request::is('admin/user/log')?'active':''}}">
                                            <a class="nav-link" href="{{route('admin.user.log')}}">

                                                <span class="text-truncate">{{translate('Logs')}}</span>
                                            </a>
                                        </li>
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </li>
                    <li class="nav-item">
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper media align-items-center right-dropdown-icon" href="javascript:;"
                            data-hs-unfold-options='{
                                    "target": "#accountNavbarDropdown",
                                    "type": "css-animation"
                                }'>
                                <div class="media-body pl-0 pr-2">
                                    <span class="card-title h5 text-right text-white"> {{translate('admin panel')}} </span>
                                    <span class="card-text text-white">{{auth('user')->user()->f_name??''}} {{auth('user')->user()->l_name??''}}</span>
                                </div>
                                <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img"
                                        src="{{auth('user')->user()->image_fullpath}}"
                                        alt="Image Description">
                                    <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                </div>
                            </a>


                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>
