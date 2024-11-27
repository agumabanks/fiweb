
<div class="col-sm-6">
    <div class="dashboard--card h-100">
        <h6 class="subtitle">{{translate('Used Balance')}}</h6>
        <h2 class="title">
            {{ Helpers::set_symbol($data['used_balance']??0) }}
        </h2>
        <img src="{{asset('public/assets/admin/img/media/dollar-1.png')}}" class="dashboard-icon" alt="{{ translate('used_balance') }}">
    </div>
</div>

<div class="col-sm-6">
    <div class="dashboard--card h-100">
        <h6 class="subtitle">{{translate('Unused Balance')}}</h6>
        <h2 class="title">
            {{ Helpers::set_symbol($data['unused_balance']??0) }}
        </h2>
        <img src="{{asset('public/assets/admin/img/media/dollar-2.png')}}" class="dashboard-icon" alt="{{ translate('unused_balance') }}">
    </div>
</div>

