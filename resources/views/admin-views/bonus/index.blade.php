@extends('layouts.admin.app')

@section('title', translate('Add New bonus'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/banner.png')}}" alt="{{ translate('bonus') }}">
            <h2 class="page-header-title">{{translate('Add New bonus')}}</h2>
        </div>

        <div class="card card-body mb-3">
            <form action="{{route('admin.bonus.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row align-items-end">
                    <div class="col-lg-6 form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('title')}}</label>
                        <input type="text" name="title" value="{{old('title')}}" class="form-control" placeholder="{{translate('title')}}" required>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label" for="exampleFormControlInput1">{{translate('User Type')}}</label>
                        <select name="user_type" class="form-control js-select2-custom" required>
                            <option value="all" selected>{{translate('All')}}</option>
                            <option value="customer">{{translate('Customer')}}</option>
                            <option value="agent">{{translate('Agent')}}</option>
                        </select>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('Minimum Add Money Amount')}}</label>
                        <input type="number" min="0" step="any" name="maximum_add_money_amount" class="form-control" value="{{old('maximum_add_money_amount')}}" required>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('limit Per User')}}
                            <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                               title="{{ translate('Each user will only receive the bonus up to the limit') }}"></i>
                        </label>
                        <input type="number" name="limit_per_user" min="1" class="form-control" value="{{old('limit_per_user')}}" required>
                    </div>

                    <div class="col-lg-4 form-group">
                        <label class="input-label">{{translate('Bonus Type')}}</label>
                        <select name="bonus_type" id="user_type" class="form-control js-select2-custom" required>
                            <option value="flat">{{translate('Flat')}}</option>
                            <option value="percentage">{{translate('Percentage')}}</option>
                        </select>
                    </div>

                    <div class="col-lg-4 form-group">
                        <label class="input-label">{{translate('bonus')}} <span id="bonus_label__span">({{\App\CentralLogics\helpers::currency_symbol()}})</span></label>
                        <input type="number" min="0" step="any" name="bonus" class="form-control" value="{{old('bonus')}}" required>
                    </div>

                    <div class="col-lg-4 form-group d-none" id="maximum_bonus_amount__div">
                        <label class="input-label">{{translate('maximum Bonus Amount')}}</label>
                        <input type="number" step="any" name="maximum_bonus_amount" id="maximum_bonus_amount" class="form-control" value="{{old('maximum_bonus_amount')}}">
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('Start Date')}}</label>
                        <input type="datetime-local" name="start_date_time" class="form-control" value="{{old('start_date_time')}}" required>
                    </div>

                    <div class="col-lg-6 form-group">
                        <label class="input-label">{{translate('End Date')}}</label>
                        <input type="datetime-local" name="end_date_time" class="form-control" value="{{old('end_date_time')}}" required>
                    </div>
                </div>
                <div class="d-flex gap-3 justify-content-end">
                    <button type="reset" class="btn btn-secondary" id="reset-btn">{{translate('reset')}}</button>
                    <button type="submit" class="btn btn-primary">{{translate('Add bonus')}}</button>
                </div>
            </form>
        </div>
        <div class="card">
            <div class="card-header __wrap-gap-10 flex-between">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('bonus Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $bonuses->total() }}</span>
                </div>
                <div>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                    class="form-control mn-md-w280"
                                    placeholder="{{translate('Search by Title')}}" aria-label="Search"
                                    value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('title')}}</th>
                        <th>{{translate('User Type')}}</th>
                        <th>{{translate('Minimum Add Money Amount')}}
                            <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="right"
                               title="{{ translate('Bonus will be applied depending on the highest valid Minimum Add Money amount') }}"></i>
                        </th>
                        <th>{{translate('Limit Per User')}}</th>
                        <th>{{translate('Bonus Type')}}</th>
                        <th>{{translate('Bonus')}}</th>
                        <th>{{translate('Maximum Bonus Amount')}}</th>
                        <th>{{translate('Start Date')}}</th>
                        <th>{{translate('End Date')}}</th>
                        <th>{{translate('Status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($bonuses as $key=>$bonus)
                        <tr>
                            <td>{{$bonuses->firstitem()+$key}}</td>
                            <td>{{substr($bonus['title'],0,25)}} {{strlen($bonus['title'])>25?'...':''}}</td>
                            <td>{{translate($bonus->user_type)}}</td>
                            <td>{{ Helpers::set_symbol($bonus->min_add_money_amount) }}</td>
                            <td>{{$bonus->limit_per_user}}</td>
                            <td>{{translate($bonus->bonus_type)}}</td>
                            <td>{{ $bonus->bonus_type == 'flat' ? Helpers::set_symbol($bonus->bonus) : $bonus->bonus.'%' }}</td>
                            <td>{{$bonus->bonus_type == 'flat' ? '-' : Helpers::set_symbol($bonus->max_bonus_amount)}}</td>
                            <td>{{$bonus->start_date_time}}</td>
                            <td>{{$bonus->end_date_time}}</td>
                            <td>
                                <label class="switcher" for="status_{{$bonus['id']}}">
                                    <input type="checkbox" name="status"
                                            class="switcher_input change-status"
                                            id="status_{{$bonus['id']}}" {{$bonus?($bonus['is_active']==1?'checked':''):''}}
                                            data-route="{{route('admin.bonus.status',[$bonus['id']])}}">
                                    <span class="switcher_control"></span>
                                </label>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                        href="{{route('admin.bonus.edit',[$bonus['id']])}}">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                    <a class="action-btn btn btn-outline-danger delete-bonus"
                                         data-id="{{ $bonus->id }}">
                                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $bonuses->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        $("#user_type").change( function() {
            let val = $("#user_type option:selected").val();
            if(val === 'percentage') {
                $("#bonus_label__span").html('(%)');
                $("#maximum_bonus_amount__div").removeClass('d-none');
                $("#maximum_bonus_amount").prop('required',true);

            } else if(val === 'flat') {
                $("#bonus_label__span").html('({{\App\CentralLogics\helpers::currency_symbol()}})');
                $("#maximum_bonus_amount__div").addClass('d-none');
                $("#maximum_bonus_amount").prop('required',false);
            }
        });

        $("#reset-btn").on('click', function (){
            $("#user_type").val('flat').trigger('change');
        });

        $('.delete-bonus').on('click', function (){
            let id = $(this).data('id');
            Swal.fire({
                title: '{{translate('Are you sure delete this Bonus')}}?',
                text: "{{translate('You will not be able to revert this')}}!",
                showCancelButton: true,
                confirmButtonColor: '#014F5B',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{translate('Yes, delete it')}}!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{route('admin.bonus.delete')}}",
                        method: 'POST',
                        data: {id: id},
                        success: function () {
                            toastr.success('{{translate('Bonus deleted successfully')}}');
                            location.reload();
                        }
                    });
                }
            })
        });
    </script>
@endpush
