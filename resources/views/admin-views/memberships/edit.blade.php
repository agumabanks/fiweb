@extends('layouts.app')

@section('content')
<div class="container">
    <h2>{{ $pageTitle }}</h2>
    <form action="{{ route('memberships.update', $membership->id) }}" method="POST">
        @csrf
        @method('PUT')
        <!-- The same fields as the create form -->
        <!-- ... -->
    </form>
</div>
@endsection
