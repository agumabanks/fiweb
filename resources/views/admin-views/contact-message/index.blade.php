@extends('layouts.admin.app')

@section('title', translate('Contact Message'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/banner.png')}}" alt="{{ translate('message') }}">
            <h2 class="page-header-title">{{translate('Contact Message')}}</h2>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10 flex-between">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Contact Message Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $contacts->total() }}</span>
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
                        <th>{{translate('name')}}</th>
                        <th>{{translate('email')}}</th>
                        <th>{{translate('message')}}</th>
                        <th>{{translate('status')}}</th>
                        <th>{{translate('action')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                        @foreach($contacts as $key => $contact)
                        <tr>
                            <td>{{$contacts->firstitem()+$key}}</td>
                            <td>
                                {{Str::limit($contact['name'],22,'...')}}
                            </td>
                            <td>
                                {{$contact['email']}}
                            </td>
                            <td>
                                {{Str::limit($contact['message'],120,'...') }}
                            </td>
                            <td>
                                @if ($contact->seen == 1)
                                    <label class="badge badge-success">{{ translate('Seen') }}</label>
                                @else
                                    <label class="badge badge-primary">{{ translate('Not_replied_Yet') }}</label>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                        href="{{route('admin.contact.view',[$contact['id']])}}">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <a class="action-btn btn btn-outline-danger delete-contact-message"
                                       data-id="{{ $contact['id'] }}"
                                       id="{{$contact['id']}}">
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
                    {!! $contacts->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        $(".delete-contact-message").on('click', function (){
            let id =  $(this).data('id')

            Swal.fire({
                title: '{{translate('Are you sure delete this message')}}?',
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
                        url: "{{route('admin.contact.delete')}}",
                        method: 'POST',
                        data: {id: id},
                        success: function () {
                            toastr.success('{{translate('Contact message delete successfully')}}');
                            location.reload();
                        }
                    });
                }
            })
        })
    </script>
@endpush
