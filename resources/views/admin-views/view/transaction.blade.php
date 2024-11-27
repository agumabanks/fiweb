@extends('layouts.admin.app')

@section('title', translate('Transaction'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/agent.png')}}" alt="{{ translate('user') }}">
            <h2 class="page-header-title">{{translate('Details')}}</h2>
        </div>

        <div class="page-header mb-3">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                @include('admin-views.view.partails.navbar')
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('transaction Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $transactions->total() }}</span>
                </div>
                <div>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                    class="form-control mn-md-w280"
                                    placeholder="{{translate('Search by ID')}}" aria-label="Search"
                                    value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('Transaction Id')}}</th>
                        <th>{{translate('Sender')}}</th>
                        <th>{{translate('Receiver')}}</th>
                        <th>{{translate('Debit')}}</th>
                        <th>{{translate('Credit')}}</th>
                        <th>{{translate('Type')}}</th>
                        <th>{{translate('Balance')}}</th>
                        <th class="text-center">{{translate('Time')}}</th>
                    </thead>

                    <tbody>
                    @foreach($transactions as $key=>$transaction)
                        <tr>
                            <td>{{$transactions->firstitem()+$key}}</td>
                            <td>{{ $transaction->transaction_id??'' }}</td>
                            @php
                                $sender = $transaction['transaction_type'] == 'payment' ? $transaction['to_user_id'] : $transaction['from_user_id'];
                                $receiver = $transaction['transaction_type'] == 'payment' ? $transaction['from_user_id'] : $transaction['to_user_id'];
                            @endphp
                            <td>
                                @php($sender_info = Helpers::get_user_info($sender))
                                @if($sender_info != null)
                                    <a class="text-dark" href="{{route('admin.customer.view',[$sender])}}">
                                        {{ $sender_info->f_name ?? '' }} {{ $sender_info->phone ?? ''}}
                                    </a>
                                @else
                                    <span class="text-muted badge badge-danger text-dark">{{ translate('User unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                @php($receiver_info = Helpers::get_user_info($receiver))
                                @if($receiver_info != null)
                                    <a class="text-dark" href="{{route('admin.customer.view',[$receiver])}}">
                                        {{ $receiver_info->f_name ?? '' }} {{ $receiver_info->phone ?? '' }}
                                    </a>
                                @else
                                    <span class="text-muted badge badge-danger text-dark">{{ translate('User unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                <span>
                                    {{ Helpers::set_symbol($transaction['debit']) }}
                                </span>
                            </td>
                            <td>
                                <span>
                                    {{ Helpers::set_symbol($transaction['credit']) }}
                                </span>
                            </td>
                            <td>
                                <span class="text-uppercase text-muted badge badge-light">{{ translate($transaction['transaction_type']) }}</span>
                            </td>
                            <td>
                                <span>{{ Helpers::set_symbol($transaction['balance']) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $transaction->created_at->diffForHumans() }}</span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $transactions->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
