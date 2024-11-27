<div class="col-sm-6 col-xl-3">
    <div class="dashboard--card h-100">
        <h6 class="subtitle">{{translate('Total Money Out')}}</h6>
        <h2 class="title">
            {{ Helpers::set_symbol($data['total_balance']??0) }}
        </h2>
        <img src="{{asset('public/assets/admin/img/media/dollar-1.png')}}" class="dashboard-icon" alt="{{ translate('generated_money') }}">
    </div>
</div>

<div class="col-sm-6 col-xl-3">
    <div class="dashboard--card h-100">
        <h6 class="subtitle">{{translate('Total Money in Expenses')}}</h6>
        <h2 class="title">
            {{ Helpers::set_symbol($data['used_balance']??0) }}
        </h2>
        <img src="{{asset('public/assets/admin/img/media/dollar-2.png')}}" class="dashboard-icon" alt="{{ translate('used_balance') }}">
    </div>
</div>

<div class="col-sm-6 col-xl-3">
    <div class="dashboard--card h-100">
        <h6 class="subtitle">{{translate('Money in Loans')}}</h6>
        <h2 class="title">
            {{ Helpers::set_symbol($data['unused_balance']??0) }}
        </h2>
        <img src="{{asset('public/assets/admin/img/media/dollar-3.png')}}" class="dashboard-icon" alt="{{ translate('unused_balance') }}">
    </div>
</div>

<div class="col-sm-6 col-xl-3">
    <div class="dashboard--card h-100">
        <h6 class="subtitle">{{translate('Total Earn from Loans')}}</h6>
        <h2 class="title">
            {{ Helpers::set_symbol($data['total_earned']??0) }}
        </h2>
        <img src="{{asset('public/assets/admin/img/media/dollar-4.png')}}" class="dashboard-icon" alt="{{ translate('earned_money') }}">
    </div>
</div>

