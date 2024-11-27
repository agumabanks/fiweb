<div class="row">
    @foreach ($guarantors as $guarantor)
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-center h-100">
            <!-- Guarantor Photo -->
            <div class="p-4">
                <img src="{{ asset('storage/' . $guarantor->photo) }}"
                     onerror="this.src='https://maslink.sanaa.co/public/assets/admin/img/160x160/img1.jpg';"
                     alt="{{ $guarantor->name }}"
                     class="rounded-circle mb-3"
                     style="width: 90px; height: 90px; object-fit: cover;">
            </div>

            <!-- Guarantor Details -->
            <div class="card-body">
                <h5 class="card-title text-dark mb-1">{{ $guarantor->name }}</h5>
                <p class="card-text text-muted">NIN: {{ $guarantor->nin }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>
