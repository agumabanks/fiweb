@extends('layouts.admin.app')

@section('title', translate('Agent Report'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <h2 class="page-header-title">{{translate('Agent Report')}}</h2>
        </div>

        <div class="card card-body">
            <table class="table table-bordered">
                <thead class="bg-success text-white">
                    <tr>
                        <th>{{ translate('Agent Name') }}</th>
                        <th>{{ translate('Number of Clients') }}</th>
                        <th>{{ translate('Total Money Out') }}</th>
                        <th>{{ translate('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($agents as $agent)
                        <tr>
                            <td>{{ $agent->f_name }} {{ $agent->l_name }}</td>
                            <td>{{ $agent->client_count }}</td>
                            <td>{{ number_format($agent->total_money_out, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.agent.client.details', $agent->id) }}" class="btn btn-info">
                                    {{ translate('View') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
