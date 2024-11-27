@extends('layouts.admin.app')

@section('title', translate('FAQ'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/faq.png')}}" alt="{{ translate('FAQ') }}">
            <h2 class="page-header-title">{{translate('FAQ')}}</h2>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{translate('FAQ')}} {{translate('Table')}} </h5>
                <button class="btn btn-primary btn-icon-split for-addFaq" data-toggle="modal" data-target="#addModal">
                    <i class="tio-add"></i>
                    <span class="text">{{translate('Add')}} {{translate('faq')}}  </span>
                </button>
            </div>
            <div class="card-">
                <div class="table-responsive">
                    <table class="table table-borderless table-align-middle card-table">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">{{translate('SL')}}</th>
                                <th scope="col">{{translate('Question')}}</th>
                                <th scope="col">{{translate('Answer')}}</th>
                                <th scope="col">{{translate('Ranking')}}</th>
                                <th scope="col">{{translate('Status')}} </th>
                                <th class="text-center">{{translate('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($helps as $k=>$help)
                            <tr>
                                <td scope="row">{{$k+1}}</td>
                                <td><div class="mn-w160">{{$help['question']}}</div></td>
                                <td><div class="mn-w400">{{$help['answer']}}</div></td>
                                <td>{{$help['ranking']}}</td>

                                <td>
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input status_id faq-status-change"
                                                data-id="{{ $help->id }}"
                                            {{$help->status == 1?'checked':''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a class="action-btn btn btn-outline-primary edit-faq"
                                            data-toggle="modal" data-target="#editModal"
                                            data-id="{{ $help->id }}">
                                            <i class="tio-edit"></i>

                                        </a>
                                        <a class="action-btn btn btn-outline-danger delete-faq"
                                            id="{{$help['id']}}"
                                           data-id="{{ $help->id }}">
                                            <i class="tio-add-to-trash"></i>
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
                        {!! $helps->links() !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" tabindex="-1" role="dialog" id="addModal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title mb-0">{{translate('Add Help Topic')}}</h4>
                        <button type="button" class="close fs-30" data-dismiss="modal" aria-label="Close"> <span
                                aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('admin.helpTopic.add-new') }}" method="post" id="addForm">
                        @csrf
                        <div class="modal-body pt-3 pb-0" style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">

                            <div class="form-group">
                                <label class=" text-dark">{{translate('Question')}}</label>
                                <input type="text" class="form-control" name="question" placeholder="{{translate('Type Question')}}">
                            </div>

                            <div class="form-group">
                                <label class=" text-dark">{{translate('Answer')}}</label>
                                <textarea class="form-control" name="answer" rows="6" placeholder="{{translate('Type Answer')}}"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ranking" class=" text-dark">{{translate('Ranking')}}</label>
                                        <input type="number" name="ranking" class="form-control" autofoucs>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="control-label mb-2 text-dark">{{translate('Status')}}</div>
                                        <label class="custom-switch d-flex align-items-center gap-2 pl-0">
                                            <input type="checkbox" name="status" id="e_status" value="1"
                                                   class="custom-switch-input">
                                            <span class="custom-switch-description">{{translate('Active')}}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
                            <button class="btn btn-primary">{{translate('Save')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="editModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title mb-0">{{translate('Edit Modal Help Topic')}}</h4>
                    <button type="button" class="close fs-30" data-dismiss="modal" aria-label="Close"> <span
                            aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="post" id="editForm" style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
                    @csrf
                    <div class="modal-body pt-3 pb-0">
                        <div class="form-group">
                            <label>{{translate('Question')}}</label>
                            <input type="text" class="form-control e_name" name="question" placeholder="{{translate('Type Question')}}"
                                   id="e_question">
                        </div>
                        <div class="form-group">
                            <label>{{translate('Answer')}}</label>
                            <textarea class="form-control" name="answer"
                                      rows="6" placeholder="{{translate('Type Answer')}}" id="e_answer"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="ranking">{{translate('Ranking')}}</label>
                            <input type="number" name="ranking" class="form-control" id="e_ranking" required>
                        </div>
                    </div>
                    <div class="modal-footer pt-0 border-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{translate('Close')}}</button>
                        <button class="btn btn-primary">{{translate('update')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('script_2')

    <script>
        "use strict";

        $('.faq-status-change').on('change', function (){
            let id = $(this).data('id');
            statusUpdate(id)
        });

        function statusUpdate(id) {
            $.ajax({
                url: "status/" + id,
                type: 'get',
                dataType: 'json',
                success: function (res) {
                    toastr.success(res.success);
                    window.location.reload();
                }

            });
        }

        $('.edit-faq').on('click', function (){
            let id = $(this).data('id');
            editItem(id)
        });

        function editItem(id) {
            $.ajax({
                url: "edit/" + id,
                type: "get",
                data: {"_token": "{{ csrf_token() }}"},
                dataType: "json",
                success: function (data) {
                    $("#e_question").val(data.question);
                    $("#e_answer").val(data.answer);
                    $("#e_ranking").val(data.ranking);
                    $("#editForm").attr("action", "update/" + data.id);
                }
            });
        }

        $('.delete-faq').on('click', function (){
            let id = $(this).data('id');
            Swal.fire({
                title: '{{translate('Are you sure delete this FAQ')}}?',
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
                        url: "{{route('admin.helpTopic.delete')}}",
                        method: 'POST',
                        data: {id: id},
                        success: function () {
                            toastr.success('{{translate('FAQ deleted successfully')}}');
                            location.reload();
                        }
                    });
                }
            })
        });

    </script>
@endpush
