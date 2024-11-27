<ul class="list-unstyled">
    <li class="{{Request::is('admin/business-settings/business-setup')?'active':''}}"><a href="{{route('admin.business-settings.business-setup')}}">{{translate('Business Settings')}}</a></li>

    <li class="{{Request::is('admin/business-settings/charge-setup')?'active':''}}"><a href="{{route('admin.business-settings.charge-setup')}}">{{translate('Charge Setup')}}</a></li>

    <li class="{{Request::is('admin/business-settings/customer-transaction-limits') || Request::is('admin/business-settings/agent-transaction-limits')?'active':''}}"><a href="{{route('admin.business-settings.customer_transaction_limits')}}">{{translate('Transaction Limits')}}</a></li>

    <li class="{{Request::is('admin/business-settings/sms-module')?'active':''}}"><a href="{{route('admin.business-settings.sms-module')}}">{{translate('SMS Module')}}</a></li>

    <li class="{{Request::is('admin/business-settings/payment-method')?'active':''}}"><a href="{{route('admin.business-settings.payment-method')}}">{{translate('Payment Methods')}}</a></li>

    <li class="{{Request::is('admin/business-settings/mail-config') || Request::is('admin/business-settings/send-mail-index') ?'active':''}}"><a href="{{route('admin.business-settings.mail_config')}}">{{translate('Mail Config')}}</a></li>

    <li class="{{Request::is('admin/business-settings/fcm-index')?'active':''}}"><a href="{{route('admin.business-settings.fcm-index')}}">{{translate('Notification Settings')}}</a></li>

    <li class="{{Request::is('admin/business-settings/app-settings')?'active':''}}"><a href="{{route('admin.business-settings.app_settings')}}">{{translate('App Settings')}}</a></li>

    <li class="{{Request::is('admin/business-settings/recaptcha')?'active':''}}"><a href="{{route('admin.business-settings.recaptcha_index')}}">{{translate('Recaptcha')}}</a></li>

    <li class="{{Request::is('admin/business-settings/language*')?'active':''}}"><a href="{{route('admin.business-settings.language.index')}}">{{translate('Languages')}}</a></li>

    <li class="{{Request::is('admin/business-settings/system-feature*')?'active':''}}"><a href="{{route('admin.business-settings.system_feature')}}">{{translate('System Feature')}}</a></li>

    <li class="{{Request::is('admin/business-settings/otp-setup*')?'active':''}}"><a href="{{route('admin.business-settings.otp_setup_index')}}">{{translate('Login and OTP Setup')}}</a></li>
</ul>
