<div class="card-header">
    <h5 class="card-header-title">
        {{translate('Top Customers')}}
    </h5>
    <a href="{{route('admin.customer.list')}}" class="fs-12px">{{translate('View All')}}</a>
</div>

<div class="card-body">
    <div class="grid-container">
        @foreach($top_customers as $key=>$top_customer)
            @if(isset($top_customer->user))
                <a class="cursor-pointer" href="{{route('admin.customer.view', [$top_customer['user_id']])}}">
                    <div class="dashboard--card h-100 p-4">
                        <div class="bg-primary text-white rounded-10 d-flex flex-column align-items-center mb-2 fs-10 px-3 py-2">
                            <span>{{ translate('Total Transaction') }} :</span>
                            <span>{{ Helpers::set_symbol($top_customer['total_transaction']) }}</span>
                        </div>

                        <div class="avatar avatar-lg border border-width-2 rounded-circle mx-auto">
                            <img class="rounded-circle img-fit"
                                 src="{{$top_customer->user['image_fullpath']}}"
                                 alt="{{ translate('customer') }}">
                        </div>
                        <div class="text-center font-weight-bolder mt-2">{{$top_customer->user['f_name']??'Not exist'}}</div>
                    </div>
                </a>
            @endif
        @endforeach
    </div>
</div>
