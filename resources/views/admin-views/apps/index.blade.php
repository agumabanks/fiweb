@extends('layouts.admin.app')

@section('title', translate('dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="card m-2">
    
    <!--apps.store-->
    <div class="m-2">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h2 class="my-4">{{ $pageTitle }}</h2>
        <a href="{{route('admin.apps.add')}}" class="btn btn-primary mb-3" >
            <i class="tio-add"></i> {{ translate('Add App') }}
        </a>
    </div>
    </div>

   

    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
            <tr>
                <th>App Names</th>
                <th>Description</th>
                <th>Version</th>
                <th>Added By</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="set-rows">
            @foreach ($apps as $app)
                <tr>
                    <td>{{ $app->name }}</td>
                    <td>{{ $app->description }}</td>
                    <td>{{ $app->version }}</td>
                    <td>{{ $app->addedBy->name ?? 'Unknown' }}</td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <a class="action-btn btn btn-outline-primary"
                               href="{{route('admin.apps.download', $app->id )}}">
                                 <i class="fa fa-eye" aria-hidden="true"></i>
                            </a>
                            <a class="action-btn btn btn-outline-info"
                               href="">
                                 <i class="fa fa-pencil" aria-hidden="true"></i>
                            </a>
                            <form action="" method="POST" onsubmit="return confirm('Are you sure you want to delete this app?');">
                                @csrf
                                <button type="submit" class="action-btn btn btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="table-responsive mt-4 px-3">
        <div class="d-flex justify-content-end">
            {!! $apps->links() !!}
            <nav id="datatablePagination" aria-label="Apps pagination"></nav>
        </div>
    </div>
</div>



@endsection

@push('script')
@endpush

@push('script_2')
@endpush
