@extends('layouts.admin.app')

@section('title', $pageTitle)

@section('content')
<div class="content container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>{{ $pageTitle }}</h2>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, NIN, email or phone" value="{{ $search }}">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($clients->isEmpty())
                <div class="alert alert-warning">{{ $emptyMessage }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped">
                       <thead>
                <tr>
                    <th>Sl No.</th> 
                    <th>Client Name</th>
                    <th>PAN</th>
                    <th>Card Status</th>
                    <th>Balance (UGX)</th>
                    <th>Actions</th> 
                </tr>
            </thead>
            <tbody>
                @foreach($clientsCardData as $index => $data)
                <tr>
                    <td>{{ $index + 1 }}</td> 
                    <td>{{ $data['client_name'] }}</td>
                    <td>{{ substr($data['client']->pan, 0, 6) . '******' . substr($data['client']->pan, -4) }}</td> 
                    <td>{{ $data['client']->card_status }}</td>
                    <td>{{ $data['client']->balance }}</td>
                    <td>
                        <button class="btn btn-sm btn-info">View Details</button> 
                        @if (!$data['client']->is_printed)
                            <!--<button class="btn btn-sm btn-secondary">Print Card</button> {{ route('admin.cards.print', $data['client']->id) }}-->
                            <a href="" target="_blank" class="btn btn-secondary">Print Card</a>

                        @else
                            <button class="btn btn-sm btn-secondary" disabled>Printed</button> 
                        @endif
                        <button class="btn btn-sm btn-{{ $data['client']->card_status == 'active' ? 'danger' : 'success' }}">
                            {{ $data['client']->card_status == 'active' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection