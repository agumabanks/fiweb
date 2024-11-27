@extends('layouts.admin.app')

@section('title', translate('Agent Verification requests'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/agent.png')}}" alt="{{ translate('agent') }}">
            <h2 class="page-header-title">{{translate('Agents')}}</h2>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Verification requests list')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $agents->total() }}</span>
                </div>
                <div class="d-flex flex-wrap gap-3">
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                   class="form-control mn-md-w280"
                                   placeholder="{{translate('Search by Name')}}" aria-label="Search"
                                   value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                            </div>
                        </div>
                    </form>

                    <a href="{{route('admin.agent.add')}}" class="btn btn-primary">
                        <i class="tio-add"></i> {{translate('Add')}} {{translate('Agents')}}
                    </a>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('name')}}</th>
                            <th>{{translate('phone')}}</th>
                            <th>{{translate('email')}}</th>
                            <th>{{translate('Identification Type')}}</th>
                            <th>{{translate('Identification Number')}}</th>
                            <th>{{translate('Identification Image')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($agents as $key=>$agent)
                        <tr>
                            <td>{{$agents->firstitem()+$key}}</td>
                            <td>
                                <a href="{{route('admin.agent.view',[$agent['id']])}}" class="media gap-3 align-items-center text-dark">
                                    <div class="avatar avatar-lg border rounded-circle">
                                        <img class="rounded-circle img-fit"
                                        src="{{$agent['image_fullpath']}}"
                                        alt="{{ translate('agent') }}">
                                    </div>
                                    <div class="media-body">
                                        {{$agent['f_name'].' '.$agent['l_name']}}
                                    </div>
                                </a>
                            </td>
                            <td>
                                <a class="text-dark" href="tel:{{$agent['phone']}}">{{$agent['phone']}}</a>
                            </td>
                            <td>
                                @if(isset($agent['email']))
                                    <a href="mailto:{{ $agent['email'] }}" class="text-dark">{{ $agent['email'] }}</a>
                                @else
                                    <span class="text-muted badge badge-danger text-dark">{{ translate('Email unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($agent['identification_type']))
                                    {{ translate($agent['identification_type'])  }}
                                @else
                                    <span class="badge badge-soft-danger">{{ translate('Type unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                @if(isset($agent['identification_number']))
                                    {{$agent['identification_number'] }}
                                @else
                                    <span class="badge badge-soft-danger">{{ translate('Number unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                <div data-toggle="" data-placement="top" title="{{translate('click for bigger view')}}">
                                    @foreach($agent['identification_image_fullpath'] as $identificationImage)
                                        <img class="rounded cursor-pointer show-identity-image" height="60px" width="120px"
                                             src="{{$identificationImage}}"
                                             alt="{{ translate('identity-image') }}"
                                             data-path="{{$identificationImage}}">
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                        href="{{route('admin.agent.view',[$agent['id']])}}">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    @if($agent['is_kyc_verified'] == 0)
                                        <a class="action-btn btn btn-outline-success"
                                        href="{{route('admin.agent.kyc_status_update',[$agent['id'], 1])}}">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                        </a>
                                        <a class="action-btn btn btn-outline-danger"
                                        href="{{route('admin.agent.kyc_status_update',[$agent['id'], 2])}}">
                                            <i class="fa fa-times" aria-hidden="true"></i>
                                        </a>
                                    @elseif($agent['is_kyc_verified'] == 2)
                                        <span class="badge badge-soft-danger"> {{translate('Denied')}}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $agents->links() !!}
                    <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-lg" id="identification_image_view_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div data-dismiss="modal">
                            <img src="{{asset('public/assets/admin/img/900x400/img1.jpg')}}" alt="{{ translate('identification-image') }}"
                                    class="w-100" id="identification_image_element">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $(".show-identity-image").on('click', function (){
            let imageLocation = $(this).data('path')

            $('#identification_image_view_modal').modal('show');
            if(imageLocation != null || imageLocation !== '') {
                $('#identification_image_element').attr("src", imageLocation);
            }
        });

    </script>
@endpush
