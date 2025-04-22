<!-- Polished Sidebar -->
<div id="sidebarMain" class="d-none" >
    <aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container">

            <!-- Brand Logo -->
            <div class="navbar-brand-wrapper justify-content-between  " style="background-color: black;">
                @php($restaurantLogo = \App\CentralLogics\helpers::get_business_settings('logo'))
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}" aria-label="Front">
                    <img class="w-100 side-logo"
                         src="https://maslink.sanaa.co/storage/app/public/business/2023-12-12-65780fd61c4d5.png"
                         alt="{{ translate('Sanaa') }}">
                </a>
                <div class="navbar-nav-wrap-content-left">
                    <!-- Toggle Button (Collapses Sidebar) -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"></i>
                    </button>
                </div>
            </div>
            <!-- End Brand Logo -->

            <!-- Content -->
            <div class="navbar-vertical-content" style="background-color: black;">

                <!-- Search Form (Optional) -->
                <form class="sidebar--search-form">
                    <div class="search--form-group">
                        <button type="button" class="btn">
                            <i class="tio-search"></i>
                        </button>
                        <input type="text" class="form-control form--control"
                               placeholder="Search Menu..." id="search-sidebar-menu">
                    </div>
                </form>
                <!-- End Search Form -->

                <ul class="navbar-nav navbar-nav-lg nav-tabs">

                    <!-- Dashboard -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                           href="{{ route('admin.dashboard') }}"
                           title="{{ translate('Dashboard') }}">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Dashboard') }}
                            </span>
                        </a>
                    </li>
                    <!-- End Dashboard -->

                    <!-- Reports -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/report*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                           href="javascript:;" title="{{ translate('Reports') }}">
                            <i class="tio-chart-bar-2 nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Reports') }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/report*') ? 'block' : 'none' }}">
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/report/adminsr') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{ route('admin.report.index') }}"
                                   title="{{ translate('Reports') }}">
                                    <i class="tio-chart-bar-1 nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('General Reports') }}
                                    </span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/monthly-reports') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{ route('admin.monthlyReports.index') }}"
                                   title="{{ translate('Reports') }}">
                                    <i class="tio-chart-bar-1 nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('Monthly Reports') }}
                                    </span>
                                </a>
                            </li>
                            
                            {{-- agent --}}
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/agent-report') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{ route('admin.agent.report') }}"
                                   title="{{ translate('Reports') }}">
                                    <i class="tio-chart-bar-4 nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('Agents Reports') }}
                                    </span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/aging') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link"
                                   href="{{ route('admin.reports.delinquency.index') }}"
                                   title="{{ translate('Reports') }}">
                                    <i class="tio-chart-bar-2 nav-icon"></i>
                                    <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                        {{ translate('Aging Reports') }}
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End Reports -->

                    

                    <!-- Loans -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/loans*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                           href="javascript:;" title="{{ translate('Loans') }}">
                            <i class="tio-money nav-icon"></i>
                            <span class="text-truncate">{{ translate('Loans') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub" style="display: {{ Request::is('admin/loans*') ? 'block' : 'none' }}">

                            <li class="navbar-vertical-aside-has-menu {{ Request::is('/admin/admin/loans/analysis') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loan.analysis.index') }}"
                                   title="{{ translate('Analysis') }}">
                                    <span class="text-truncate">Analysis</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('/admin/loan-arrears') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loan-arrears.index') }}"
                                   title="{{ translate('Arrears Loans') }}">
                                    <span class="text-truncate">{{ translate('Arrears') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('/admin/loan-arrears') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loan-advances.index') }}"
                                   title="{{ translate('Arrears Loans') }}">
                                    <span class="text-truncate">{{ translate('Advances') }}</span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/pendingLoans') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loan-pendingLoans') }}"
                                   title="{{ translate('Pending Loans') }}">
                                    <span class="text-truncate">{{ translate('Pending') }}</span>
                                </a>
                            </li>
                            {{-- <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/runningLoans') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loan-runningLoans') }}"
                                   title="{{ translate('Running Loans') }}">
                                    <span class="text-truncate">{{ translate('Running Loans') }}</span>
                                </a>
                            </li> --}}
                            {{-- <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/dueloans') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loans-due') }}"
                                   title="{{ translate('Due Loans') }}">
                                    <span class="text-truncate">{{ translate('Due Loans') }}</span>
                                </a>
                            </li> --}}
                            {{-- <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/paidLoans') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.paidLoans') }}"
                                   title="{{ translate('Paid Loans') }}">
                                    <span class="text-truncate">{{ translate('Paid Loans') }}</span>
                                </a>
                            </li> --}}
                            {{-- <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/rejectedLoans') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loan-loanrejectedLoans') }}"
                                   title="{{ translate('Rejected Loans') }}">
                                    <span class="text-truncate">{{ translate('Rejected Loans') }}</span>
                                </a>
                            </li> --}}
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/all-loans') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.all-loans') }}"
                                   title="{{ translate('All Loans') }}">
                                    <span class="text-truncate">{{ translate('All Loans') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/loan-plans') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.loan-plans') }}"
                                   title="{{ translate('Loan Plans') }}">
                                    <span class="text-truncate">{{ translate('Loan Plans') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End Loans -->

                    <!-- Transactions -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/transactions*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                           href="javascript:;" title="{{ translate('Transactions') }}">
                            <i class="tio-chart-bar-1 nav-icon"></i>
                            <span class="text-truncate">{{ translate('Transactions') }}</span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/transactions*') ? 'block' : 'none' }}">
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/client/cards*') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.client.cards') }}"
                                   title="{{ translate('Sanaa Cards') }}">
                                    <span class="text-truncate">{{ translate('Sanaa Cards') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/expense/expenses') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.expense.expenses') }}"
                                   title="{{ translate('Cash Flows') }}">
                                    <span class="text-truncate">{{ translate('Cash Flows') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/agent-report-transaction') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.agent.trans', ['trx_type' => 'all']) }}"
                                   title="{{ translate('Transactions') }}">
                                    <span class="text-truncate">{{ translate('Transactions History') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End Transactions -->

                    <!-- Clients -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/clients*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                           href="javascript:;" title="{{ translate('Clients') }}">
                            <i class="tio-user-big-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Clients') }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/clients*') ? 'block' : 'none' }}">
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/clients/active') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.clients.active') }}"
                                   title="{{ translate('Active Clients') }}">
                                    <span class="text-truncate">{{ translate('Active Clients') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/clients/banned') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.clients.banned') }}"
                                   title="{{ translate('Banned Clients') }}">
                                    <span class="text-truncate">{{ translate('Banned Clients') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/clients/with-balance') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.clients.with-balance') }}"
                                   title="{{ translate('Client Memberships') }}">
                                    <span class="text-truncate">{{ translate('Client Memberships') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/clients/add') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.clients.add') }}"
                                   title="{{ translate('Add Client') }}">
                                    <span class="text-truncate">{{ translate('Add Client') }}</span>
                                </a>

                                
                            </li>


                            

                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/allclients') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.allclients') }}"
                                   title="{{ translate('All Clients') }}">
                                    <span class="text-truncate">{{ translate('All Clients') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End Clients -->

                    <!-- Management -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/management*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                           href="javascript:;" title="{{ translate('Management') }}">
                            <i class="tio-settings nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('Management') }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/management*') ? 'block' : 'none' }}">
                            <!-- Branches -->
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/branches*') ? 'active' : '' }}">
                                <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                                   href="javascript:;" title="{{ translate('Branches') }}">
                                    <span class="text-truncate">{{ translate('Branches') }}</span>
                                </a>
                                <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                    style="display: {{ Request::is('admin/branches*') ? 'block' : 'none' }}">
                                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/branches') ? 'active' : '' }}">
                                       
                                    <a class="nav-link"
                                           href=""
                                           title="{{ translate('All Branches') }}">
                                            <span class="text-truncate">{{ translate('All Branches') }}</span>
                                        </a>
                                    </li>
                                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/agent/list') ? 'active' : '' }}">
                                        <a class="nav-link"
                                           href="{{ route('admin.agent.list') }}"
                                           title="{{ translate('Branches Staff') }}">
                                            <span class="text-truncate">{{ translate('Branches Staff') }}</span>
                                        </a>
                                    </li>
                                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/teller/list') ? 'active' : '' }}">
                                        <a class="nav-link"
                                           href="{{ route('admin.teller.list') }}"
                                           title="{{ translate('Tellers') }}">
                                            <span class="text-truncate">{{ translate('Tellers') }}</span>
                                        </a>
                                    </li>
                                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/admin/apps') ? 'active' : '' }}">
                                        <a class="nav-link"
                                           href="{{ route('admin.apps.index') }}"
                                           title="{{ translate('Sanaa Apps') }}">
                                            <span class="text-truncate">{{ translate('Sanaa Apps') }}</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End Branches -->
                        </ul>
                    </li>
                    <!-- End Management -->

                    <!-- System -->
                    <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/system*') ? 'active' : '' }}">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                           href="javascript:;" title="{{ translate('System') }}">
                            <i class="tio-dashboard nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                {{ translate('System') }}
                            </span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: {{ Request::is('admin/system*') ? 'block' : 'none' }}">
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/user/log') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.user.log') }}"
                                   title="{{ translate('User Logs') }}">
                                    <span class="text-truncate">{{ translate('User Logs') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/access/history') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.access.history') }}"
                                   title="{{ translate('Access History') }}">
                                    <span class="text-truncate">{{ translate('Access History') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/notification/history') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.notification.history') }}"
                                   title="{{ translate('Notification History') }}">
                                    <span class="text-truncate">{{ translate('Notification History') }}</span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/settings') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.business-settings.business-setup') }}"
                                   title="{{ translate('System Settings') }}">
                                    <span class="text-truncate">{{ translate('System Settings') }}</span>
                                </a>
                            </li>

                            <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/settings') ? 'active' : '' }}">
                                <a class="nav-link"
                                   href="{{ route('admin.business-settings.business-setup') }}"
                                   title="{{ translate('System Settings') }}">
                                    <span class="text-truncate">{{ translate('How to do what?') }}</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- End System -->
                </ul>
            </div>
            <!-- End Content -->
        </div>

        <!-- Footer (User Panel) -->
            <div class="navbar-vertical-footer" style="background-color: black; flex-shrink: 0;">
                <div class="hs-unfold w-100">
                    <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper d-flex align-items-center justify-content-between p-2" 
                    href="javascript:void(0);"
                    data-hs-unfold-options='{
                        "target": "#accountDropdown",
                        "type": "css-animation",
                        "event": "hover"
                    }'
                    style="transition: all 0.2s ease; border-left: 3px solid transparent;">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm avatar-circle mr-2">
                                <img class="avatar-img" src="{{auth('user')->user()->image_fullpath}}" alt="Image Description">
                                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                            </div>
                            <div class="media-body">
                                <span class="card-title text-white h6 mb-0">{{auth('user')->user()->f_name??''}} {{auth('user')->user()->l_name??''}}</span>
                                <small class="card-text d-block text-white-50">{{auth('user')->user()->phone??''}}</small>
                            </div>
                        </div>
                        <i class="tio-chevron-right text-white ml-1 user-dropdown-indicator"></i>
                    </a>

                    <div id="accountDropdown" class="hs-unfold-content dropdown-menu dropdown-menu-right" 
                        style="width: 18rem; box-shadow: 0 10px 40px 10px rgba(0, 0, 0, 0.15); border-radius: 8px; margin-bottom: 10px;">
                        <div class="dropdown-item-text p-3">
                            <div class="media align-items-center">
                                <div class="avatar avatar-lg avatar-circle mr-3">
                                    <img class="avatar-img" src="{{auth('user')->user()->image_fullpath}}" alt="Image Description">
                                </div>
                                <div class="media-body">
                                    <span class="card-title h5 mb-0">{{auth('user')->user()->f_name??''}} {{auth('user')->user()->l_name??''}}</span>
                                    <span class="card-text d-block">{{auth('user')->user()->phone??''}}</span>
                                    <a href="{{route('admin.settings')}}" class="small text-primary">My Sanaa account</a>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown-divider my-1"></div>

                        <!-- Account Options -->
                        <a class="dropdown-item d-flex align-items-center py-2 px-3" href="{{route('admin.settings')}}">
                            <i class="tio-user-outlined mr-2"></i>
                            <span class="text-truncate pr-2">My Profile</span>
                        </a>
                        
                        <a class="dropdown-item d-flex align-items-center py-2 px-3" href="#">
                            <i class="tio-star mr-2"></i>
                            <span class="text-truncate pr-2">View free benefits</span>
                            <span class="badge badge-soft-info ml-auto">New</span>
                        </a>
                        
                        <a class="dropdown-item d-flex align-items-center py-2 px-3" href="{{ route('admin.business-settings.business-setup') }}">
                            <i class="tio-settings-outlined mr-2"></i>
                            <span class="text-truncate pr-2">Settings</span>
                        </a>

                        <div class="dropdown-divider my-1"></div>

                        <a class="dropdown-item d-flex align-items-center py-2 px-3" href="{{route('admin.auth.logout')}}">
                            <i class="tio-logout-outlined mr-2"></i>
                            <span class="text-truncate pr-2">Sign out</span>
                        </a>
                    </div>
                </div>
            </div>
        <!-- End Footer -->
            <!-- End Footer -->
    </aside>

    
</div>

<!-- Compact Sidebar (Optional) -->
<div id="sidebarCompact" class="d-none"></div>

@push('script_2')
<!-- Additional scripts or sidebar toggling JS can go here -->
<script>
    $(document).ready(function() {
        // Initialize HSUnfold components with hover option
        $('.js-hs-unfold-invoker').each(function() {
            var unfold = new HSUnfold($(this), {
                event: 'hover',
                delay: {
                    show: 100,
                    hide: 300
                }
            }).init();
        });
        
        // Handle chevron rotation
        $('.navbar-vertical-footer .js-hs-unfold-invoker').hover(
            function() {
                $('.user-dropdown-indicator').css('transform', 'rotate(90deg)');
                $(this).addClass('active');
            },
            function() {
                if (!$('.navbar-vertical-footer .hs-unfold').hasClass('show')) {
                    $('.user-dropdown-indicator').css('transform', 'rotate(0)');
                    $(this).removeClass('active');
                }
            }
        );
        
        // Also handle clicks for mobile/touch devices
        $('.navbar-vertical-footer .js-hs-unfold-invoker').on('click', function(e) {
            e.preventDefault();
            
            if ($('#accountDropdown').is(':visible')) {
                $('#accountDropdown').removeClass('show').addClass('hide');
                $('.user-dropdown-indicator').css('transform', 'rotate(0)');
                $(this).removeClass('active');
            } else {
                $('#accountDropdown').removeClass('hide').addClass('show');
                $('.user-dropdown-indicator').css('transform', 'rotate(90deg)');
                $(this).addClass('active');
            }
        });
        
        // Keep dropdown open when hovering on the dropdown itself
        $('#accountDropdown').hover(
            function() {
                $(this).addClass('show');
                $('.navbar-vertical-footer .js-hs-unfold-invoker').addClass('active');
                $('.user-dropdown-indicator').css('transform', 'rotate(90deg)');
            },
            function() {
                setTimeout(function() {
                    if (!$('.navbar-vertical-footer .js-hs-unfold-invoker:hover').length) {
                        $('#accountDropdown').removeClass('show');
                        $('.navbar-vertical-footer .js-hs-unfold-invoker').removeClass('active');
                        $('.user-dropdown-indicator').css('transform', 'rotate(0)');
                    }
                }, 200);
            }
        );
    });
</script>
@endpush
