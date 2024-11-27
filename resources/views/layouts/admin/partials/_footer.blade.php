<div class="footer">
    <div class="row justify-content-between align-items-center mt-2">
        <div class="col-md-4">
            <p class="text-center text-md-left mb-2 mb-md-0">
                &copy; {{\App\CentralLogics\Helpers::get_business_settings('business_name')}}.
                <span>{{\App\CentralLogics\Helpers::get_business_settings('footer_text')}}</span>
            </p>
        </div>
        <div class="col-md-8">
            <div class="d-flex justify-content-center justify-content-md-end">
                <ul class="list-inline list-separator d-flex align-items-center flex-wrap justify-content-center">
                    <li class="list-inline-item">
                        <!--<a class="list-separator-link" href="{{ route('admin.business-settings.business-setup') }}">{{translate('business')}} {{translate('setup')}}</a>-->
                    </li>

                    <li class="list-inline-item">
                        <a class="list-separator-link" href="{{ route('admin.settings') }}">{{translate('profile')}}</a>
                    </li>

                    <li class="list-inline-item">
                        <div class="hs-unfold">
                            <a class="px-2"
                               href="{{route('admin.dashboard')}}">
                                <i class="tio-home-outlined"></i>
                            </a>
                        </div>
                    </li>
                    
                </ul>
            </div>
        </div>
    </div>
</div>
