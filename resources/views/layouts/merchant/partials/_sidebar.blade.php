<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                @php($logo=\App\CentralLogics\helpers::get_business_settings('logo'))
                <a class="navbar-brand" href="{{route('merchant.dashboard')}}" aria-label="Front">
                    <img class="w-100 side-logo"
                         src="{{Helpers::onErrorImage($logo, asset('storage/app/public/business') . '/' . $logo, asset('public/assets/admin/img/1920x400/img2.jpg'), 'business/')}}"
                         alt="Logo">
                </a>

                <div class="navbar-nav-wrap-content-left">
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"></i>
                    </button>
                </div>
            </div>

            <div class="navbar-vertical-content search-bar-merchant">
                <form class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn"><i class="tio-search"></i></button>
                        <input type="text" class="form-control form--control" placeholder="Search Menu..."
                               id="search-sidebar-menu">
                    </div>
                </form>

                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <li class="navbar-vertical-aside-has-menu {{Request::is('merchant')?'show':''}}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                           href="{{route('merchant.dashboard')}}" title="{{translate('dashboard')}}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{translate('dashboard')}}
                            </span>
                        </a>
                    </li>
                    <li class="navbar-vertical-aside-has-menu {{Request::is('merchant/transaction')?'show':''}}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                           href="{{route('merchant.transaction', ['trx_type'=>'all'])}}"
                           title="{{translate('dashboard')}}">
                            <i class="tio-money-vs nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{translate('transaction')}}
                            </span>
                        </a>
                    </li>
                    <li class="navbar-vertical-aside-has-menu {{Request::is('merchant/withdraw*')?'active':''}}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                        >
                            <i class="tio-settings nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('Withdraw')}}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{Request::is('merchant/withdraw*')?'block':'none'}}">
                            <li class="navbar-vertical-aside-has-menu {{Request::is('merchant/withdraw/request')?'active':''}}">
                                <a class="nav-link " href="{{route('merchant.withdraw.request')}}"
                                   title="{{translate('withdraw')}} {{translate('request')}}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span
                                        class="text-truncate">{{translate('withdraw')}} {{translate('request')}}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{Request::is('merchant/withdraw/list')?'active':''}}">
                                <a class="nav-link "
                                   href="{{route('merchant.withdraw.list', ['request_status'=>'all'])}}"
                                   title="{{translate('request')}} {{translate('list')}}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{translate('request')}} {{translate('list')}} </span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="navbar-vertical-aside-has-menu {{Request::is('merchant/business-settings*')?'active':''}} mb-4">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                        >
                            <i class="tio-settings-outlined nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{translate('developer')}}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{Request::is('merchant/business-settings*')?'block':'none'}}">
                            <li class="navbar-vertical-aside-has-menu {{Request::is('merchant/business-settings/shop-settings')?'active':''}}">
                                <a class="nav-link " href="{{route('merchant.business-settings.shop-settings')}}"
                                   title="{{translate('shop')}} {{translate('settings')}}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{translate('shop')}} {{translate('settings')}}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{Request::is('merchant/business-settings/integration-settings*')?'active':''}}">
                                <a class="nav-link " href="{{route('merchant.business-settings.integration-settings')}}"
                                   title="{{translate('integration')}} {{translate('settings')}}">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate">{{translate('integration')}}</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>
            </div>
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none">

</div>

@push('script_2')
    <script>
        $(window).on('load', function () {
            if ($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 10);
            }
        });

        var $rows = $('.navbar-vertical-content  .navbar-nav > li');
        $('#search-sidebar-menu').keyup(function () {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

            $rows.show().filter(function () {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
    </script>
@endpush
