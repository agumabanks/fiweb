@if ($collaterals && $collaterals->count() > 0)
    <div class="row">
        @foreach ($collaterals as $collateral)
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm">
                    @if (Str::startsWith($collateral->mime_type, 'image/'))
                        {{-- <img src="{{ asset('storage/' . $collateral->file_path) }}" class="card-img-top" alt="{{ $collateral->title }}"> --}}
                        {{-- <img src="{{ Storage::url($collateral->file_path) }}" class="card-img-top" alt="{{ $collateral->title }}"> --}}
                        <img src="https://maslink.sanaa.co/storage/app/public/{{ $collateral->file_path }}" class="card-img-top" alt="{{ $collateral->title }}">



                        
                    @else
                        <img src="{{ asset('images/file-placeholder.png') }}" class="card-img-top" alt="{{ $collateral->title }}">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $collateral->title }}</h5>
                        <p class="card-text">{{ $collateral->description }}</p>
                        <a href="https://maslink.sanaa.co/storage/app/public/{{ $collateral->file_path }}" target="_blank" class="btn btn-primary btn-sm">View File</a>
                        

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <p class="text-muted">No collaterals recorded for this client.</p>
@endif
