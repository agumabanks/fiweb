<table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
    <thead class="thead-light">
        <tr>
            <th>{{ translate('Client') }}</th>
            <th>{{ translate('Phone') }}</th>
            <th>{{ translate('Credit Balance') }}</th>
            <th>{{ translate('Business') }}</th>
            <th>{{ translate('Payment Status') }}</th>
            <th>{{ translate('Added By') }}</th>
            <th>{{ translate('Action') }}</th>
        </tr>
    </thead>

    <tbody id="set-rows">
        @forelse ($clientsData as $data)
            <tr>
                <td>{{ $data['client']->name }}</td>
                <td>{{ $data['client']->phone }}</td>
                <td>{{ number_format($data['client']->credit_balance, 0) }}</td>
                <td>{{ $data['client']->business }}</td>
                <td>{{ $data['payment_status'] }}</td>
                <td>{{ $data['added_by_name'] }}</td>
                <td>
                    <div class="d-flex justify-content-center gap-2">
                        <a class="action-btn btn btn-outline-primary"
                        href="{{ route('admin.loans.admin.pay',  $data['client']->id) }}" >
                         <i class="fa fa-credit-card" aria-hidden="true"></i>
                     </a>
                        
                        <a class="action-btn btn btn-outline-primary"
                           href="{{ route('admin.clients.profile', $data['client']->id) }}">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </a>
                        <a class="action-btn btn btn-outline-info"
                           href="{{ route('admin.clients.edit', $data['client']->id) }}">
                            <i class="fa fa-pencil" aria-hidden="true"></i>
                        </a>

                        <form action="{{ route('admin.clients.delete', $data['client']->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">
                            @csrf
                            <button type="submit" class="action-btn btn btn-outline-danger">
                                <i class="fa fa-trash"></i>
                            </button>
                        </form>
                        
                        <!--<form action="{{ route('admin.clients.delete', $data['client']->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">-->
                        <!--    @csrf-->
                        <!--    <button type="submit" class="action-btn btn btn-outline-danger">-->
                        <!--        <i class="fa fa-trash"></i>-->
                        <!--    </button>-->
                        <!--</form>-->
                    </div>
                </td>
            </tr>
        @empty
            <!-- Display a message when no clients are found -->
            <tr>
                <td colspan="7" class="text-center">{{ $emptyMessage }}</td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Pagination -->
<div class="table-responsive mt-4 px-3">
    <div class="d-flex justify-content-end">
        {!! $clients->links('admin-views.clients.partials.ajax') !!}
    </div>
</div>
