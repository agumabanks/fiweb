@extends('layouts.admin.app')

@section('title', translate('Social Media'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title text-capitalize">
                        <div class="card-header-icon d-inline-flex mr-2 img">
                            <img src="{{asset('/public/assets/admin/img/social.png')}}" alt="{{ translate('social') }}">
                        </div>
                        <span>{{translate('Social Media')}}</span>
                    </h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form class="text-left" action="javascript:">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label">{{translate('Select social media')}}</label>
                                        <select class="form-control w-100" name="name" id="name" required>
                                            <option disabled selected>---{{translate('Select Social Media')}}---</option>
                                            <option value="instagram">{{translate('Instagram')}}</option>
                                            <option value="facebook">{{translate('Facebook')}}</option>
                                            <option value="twitter">{{translate('Twitter')}}</option>
                                            <option value="linkedin">{{translate('LinkedIn')}}</option>
                                            <option value="pinterest">{{translate('Pinterest')}}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="hidden" id="id">
                                        <label for="link" class="ml-1">{{ translate('social_media_link')}}
                                            <span class="input-label-secondary text--title" data-toggle="tooltip" data-placement="right" data-original-title='{{translate("Make_sure_to_include_'https://'_to_ensure_correct_functionality.")}}'>
                                                <i class="tio-info-outined"></i>
                                            </span>
                                        </label>
                                        <input type="text" name="link" class="form-control" id="link"
                                            placeholder="{{ translate('Ex :facebook.com/your-page-name') }} " required>
                                    </div>
                                    <input type="hidden" id="id">
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="reset" class="btn btn-reset text-white">{{ translate('reset')}}</button>
                                        <a id="update" class="btn btn-primary initial-hidden" href="javascript:">{{ translate('update')}}</a>
                                        <button id="add" class="btn btn-primary">{{ translate('save')}}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table" id="dataTable" width="100%" cellspacing="0">
                            <thead class="thead-light">
                            <tr>
                                <th class="border-0" scope="col">
                                    <div class="pl-2">{{ translate('sl') }}</div>
                                </th>
                                <th class="border-0" scope="col">{{ translate('name')}}</th>
                                <th class="border-0" scope="col">{{ translate('social_media_link')}}</th>
                                <th class="border-0" scope="col">{{ translate('status')}}</th>
                                <th class="border-0 w-120px text-center" scope="col">{{ translate('action')}}</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="toggle-status-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-5">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="" class="mb-5">
                                <h5 class="modal-title" id="toggle-status-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-status-message">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn-primary min-w-120 mr-3" data-dismiss="modal"
                                    onclick="confirmStatusToggle()">{{translate('Ok')}}</button>
                            <button id="reset_btn" type="reset" class="btn btn-warning min-w-120" data-dismiss="modal">
                                {{translate("Cancel")}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        fetch_social_media();

        function fetch_social_media() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.pages.social-media.fetch')}}",
                method: 'GET',
                success: function (data) {

                    if (data.length != 0) {
                        var html = '';
                        for (var count = 0; count < data.length; count++) {
                            html += '<tr>';
                            html += '<td class="column_name" data-column_name="sl" data-id="' + data[count].id + '">' + '<div class="pl-4">'+ (count + 1) +'</div>' + '</td>';
                            html += '<td class="column_name uppercase" data-column_name="name" data-id="' + data[count].id + '">' + data[count].name + '</td>';
                            html += '<td class="column_name" data-column_name="slug" data-id="' + data[count].id + '">' + data[count].link + '</td>';
                            html += `<td class="column_name" data-column_name="status" data-id="${data[count].id}">
                                <label class="toggle-switch toggle-switch-sm" for="${data[count].id}">
                                    <input type="checkbox" class="toggle-switch-input status" id='${data[count].id}' ${data[count].status == 1 ? "checked" : ""}
                                    onclick="toogleStatusModal(event,'${data[count].id}','${data[count].name.toLowerCase()}-on.png','${data[count].name.toLowerCase()}-off.png',
                                    '<strong>${data[count].name} {{translate('is_Enabled!')}}',
                                        '<strong> ${data[count].name} {{translate('is_Disabled!')}}',
                                        '<p> ${data[count].name} {{translate('is_enabled_now_everybody_can_use_or_see_this_Social_Medial')}}</p>',
                                    ' <p>${data[count].name} {{translate('is_disabled_now_no_one_can_use_or_see_this_Social_Medial')}}</p>')"
                                        >
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <form action="{{route('admin.pages.social-media.status-update')}}" method="get" id="${data[count].id}_form">
                                    <input type="hidden" name="id" value="${data[count].id}">
                                            </form>
                            </td>`;
                            html += '<td> <div class="btn--container justify-content-center"><a type="button" class="btn btn-outline-primary btn--primary action-btn edit" id="' + data[count].id + '"><i class="tio-edit"></i></a></div> </td></tr>';
                        }
                        $('tbody').html(html);
                    }
                }
            });
        }

        $('#add').on('click', function () {
            var name = $('#name').val();
            var link = $('#link').val();

            if (name == null || name == '') {
                toastr.error('{{translate('Social Name Is Requeired')}}.');
                return false;
            }
            if (link == "") {
                toastr.error('{{translate('Social Link Is Requeired')}}.');
                return false;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('admin.pages.social-media.store')}}",
                method: 'POST',
                data: {
                    name: name,
                    link: link
                },
                success: function (response) {
                    if (response.error == 1) {
                        toastr.error('{{translate('Social Media Already taken')}}');
                    } else {
                        toastr.success('{{translate('Social Media inserted Successfully')}}.');
                    }
                    $('#name').val('');
                    $('#link').val('');
                    fetch_social_media();
                }
            });
        });
        $('#update').on('click', function () {
            $('#update').attr("disabled", true);
            var id = $('#id').val();
            var name = $('#name').val();
            var link = $('#link').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{url('admin/pages/social-media')}}/"+id,
                method: 'PUT',
                data: {
                    id: id,
                    name: name,
                    link: link,
                },
                success: function (data) {
                    $('#name').attr('value', '');
                    $('#link').val('');

                    toastr.success('{{translate('Social info updated Successfully')}}.');
                    $('#update').hide();
                    $('#add').show();
                    fetch_social_media();

                }
            });
            $('#save').hide();
        });
        $(document).on('click', '.delete', function () {
            var id = $(this).attr("id");
            if (confirm("{{translate('Are you sure delete this social media')}}?")) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{url('admin/pages/social-media/destroy')}}/"+id,
                    method: 'POST',
                    data: {id: id},
                    success: function (data) {
                        fetch_social_media();
                        toastr.success('{{translate('Social media deleted Successfully')}}.');
                    }
                });
            }
        });
        $(document).on('click', '.edit', function () {
            $('#update').show();
            $('#add').hide();
            var id = $(this).attr("id");
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{url('admin/pages/social-media')}}/"+id,
                method: 'GET',
                success: function (data) {
                    $(window).scrollTop(0);
                    $('#id').val(data.id);
                    $('#name').val(data.name).trigger('change');
                    $('#link').val(data.link);
                    fetch_social_media()
                }
            });
        });

    function toogleStatusModal(e, toggle_id, on_image, off_image, on_title, off_title, on_message, off_message) {
        e.preventDefault();
        if ($('#'+toggle_id).is(':checked')) {
            $('#toggle-status-title').empty().append(on_title);
            $('#toggle-status-message').empty().append(on_message);
            $('#toggle-status-image').attr('src', "{{asset('/public/assets/admin/img/modal')}}/"+on_image);
            $('#toggle-status-ok-button').attr('toggle-ok-button', toggle_id);
        } else {
            $('#toggle-status-title').empty().append(off_title);
            $('#toggle-status-message').empty().append(off_message);
            $('#toggle-status-image').attr('src', "{{asset('/public/assets/admin/img/modal')}}/"+off_image);
            $('#toggle-status-ok-button').attr('toggle-ok-button', toggle_id);
        }
        $('#toggle-status-modal').modal('show');
    }

        function confirmStatusToggle() {

            var toggle_id = $('#toggle-status-ok-button').attr('toggle-ok-button');
            if ($('#'+toggle_id).is(':checked')) {
                $('#'+toggle_id).prop('checked', false);
                $('#'+toggle_id).val(0);
            } else {
                $('#'+toggle_id).prop('checked', true);
                $('#'+toggle_id).val(1);
            }
            $('#'+toggle_id+'_form').submit();

        }
    </script>
@endpush
