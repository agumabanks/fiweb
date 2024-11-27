@extends('layouts.merchant.app')

@section('title', translate('Transaction'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="22" src="{{asset('public/assets/admin/img/media/lending.png')}}" alt="{{translate('image')}}">
            <h1 class="page-header-title">{{translate('transaction')}}</h1>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom gap-3 mb-3">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {{$transactionTypes=='all'?'active':''}}"
                       href="{{url()->current()}}?trx_type=all">
                        {{translate('all')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$transactionTypes=='debit'?'active':''}}"
                       href="{{url()->current()}}?trx_type=debit">
                        {{translate('debit')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$transactionTypes=='credit'?'active':''}}"
                       href="{{url()->current()}}?trx_type=credit">
                        {{translate('credit')}}
                    </a>
                </li>
            </ul>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10 flex-between">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('transaction Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $transactions->total() }}</span>
                </div>
                <div>
                    <form action="{{url()->current()}}?trx_type={{$transactionTypes}}" method="GET">
                        <div class="input-group">
                            <input type="hidden" name="trx_type" value="{{$transactionTypes}}">

                            <input id="datatableSearch_" type="search" name="search"
                                   class="form-control mn-md-w280"
                                   placeholder="{{translate('Search by transaction id')}}" aria-label="Search"
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
                        class="table table-borderless table-nowrap table-align-middle card-table table-striped">
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
                        <th>{{translate('Time')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($transactions as $key=>$transaction)
                        <tr>
                            <td>{{$transactions->firstitem()+$key}}</td>
                            <td>{{ $transaction->transaction_id??'' }}</td>
                            <td>
                                @php($senderInfo = Helpers::get_user_info($transaction['from_user_id']))
                                @if($senderInfo != null)
                                    <div>
                                        <span>{{ $senderInfo->f_name ?? '' }}</span>
                                    </div>
                                    <div>
                                        <a class="text-dark"
                                           href="tel:{{ $senderInfo->phone ?? ''}}">{{ $senderInfo->phone ?? ''}}</a>
                                    </div>

                                @else
                                    <span
                                            class="text-muted badge badge-danger text-dark">{{ translate('User unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                @php($receiverInfo = Helpers::get_user_info($transaction['to_user_id']))
                                @if($receiverInfo != null)
                                    <div>
                                        <span>{{ $receiverInfo->f_name ?? '' }}</span>
                                    </div>
                                    <div>
                                        <a class="text-dark"
                                           href="tel:{{ $receiverInfo->phone ?? '' }}">{{ $receiverInfo->phone ?? '' }}</a>
                                    </div>
                                @else
                                    <span
                                            class="text-muted badge badge-danger text-dark">{{ translate('User unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="">
                                    {{ Helpers::set_symbol($transaction['debit']) }}
                                </span>
                            </td>
                            <td>
                                <span class="">
                                    {{ Helpers::set_symbol($transaction['credit']) }}
                                </span>
                            </td>
                            <td>
                                <span
                                        class="text-uppercase text-muted badge badge-light">{{ translate($transaction['transaction_type']) }}</span>
                            </td>
                            <td>
                                <span class="">{{ Helpers::set_symbol($transaction['balance']) }}</span>
                            </td>
                            <td>
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
