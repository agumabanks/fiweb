@extends('layouts.admin.app')

@section('content')
<div class="container my-5">
    <h1>Edit Client</h1>

    <form action="{{ route('admin.clients.update', $client->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Client Information Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Client Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <!-- Name -->
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" value="{{ old('name', $client->name) }}" class="form-control" required>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}" class="form-control" required>
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" name="phone" value="{{ old('phone', $client->phone) }}" class="form-control">
                        </div>

                        <!-- Address -->
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea name="address" class="form-control">{{ old('address', $client->address) }}</textarea>
                        </div>

                        <!-- Date of Birth -->
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" value="{{ old('dob', $client->dob) }}" class="form-control">
                        </div>

                        <!-- Next of Kin -->
                        <div class="form-group">
                            <label for="next_of_kin">Next of Kin</label>
                            <input type="text" name="next_of_kin" value="{{ old('next_of_kin', $client->next_of_kin) }}" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Business -->
                        <div class="form-group">
                            <label for="business">Business</label>
                            <input type="text" name="business" value="{{ old('business', $client->business) }}" class="form-control">
                        </div>

                        <!-- NIN -->
                        <div class="form-group">
                            <label for="nin">NIN</label>
                            <input type="text" name="nin" value="{{ old('nin', $client->nin) }}" class="form-control">
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ old('status', $client->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $client->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <!-- KYC Verified At -->
                        <div class="form-group">
                            <label for="kyc_verified_at">KYC Verified At</label>
                            <input type="date" name="kyc_verified_at" value="{{ old('kyc_verified_at', $client->kyc_verified_at) }}" class="form-control">
                        </div>

                        <!-- Added By -->
                        <div class="form-group">
                            <label for="added_by">Added By</label>
                            <select name="added_by" class="form-control" required>
                                <option value="">Select User</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('added_by', $client->added_by) == $user->id ? 'selected' : '' }}>
                                        {{ $user->f_name .' '. $user->l_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Credit Balance -->
                        <div class="form-group">
                            <label for="credit_balance">Credit Balance</label>
                            <input type="number" step="0.01" name="credit_balance" value="{{ old('credit_balance', $client->credit_balance) }}" class="form-control" required>
                        </div>

                        <!-- Savings Balance -->
                        <div class="form-group">
                            <label for="savings_balance">Savings Balance</label>
                            <input type="number" step="0.01" name="savings_balance" value="{{ old('savings_balance', $client->savings_balance) }}" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" class="btn btn-primary">Update Client</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Add Guarantor Section -->
    <div class="content container-fluid">
        <h1 class="mb-4">Add Guarantor</h1>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.clients.addClientGuarantorWeb', $client->id) }}" method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Name -->
                            <div class="form-group">
                                <label for="name">Guarantor Name</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>

                            <!-- NIN -->
                            <div class="form-group">
                                <label for="nin">NIN</label>
                                <input type="text" name="nin" class="form-control" value="{{ old('nin') }}" required>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Address -->
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}">
                            </div>

                            <!-- Relationship -->
                            <div class="form-group">
                                <label for="client_relationship">Relationship to Client</label>
                                <input type="text" name="client_relationship" class="form-control" value="{{ old('client_relationship') }}" required>
                            </div>

                            <!-- Job -->
                            <div class="form-group">
                                <label for="job">Job</label>
                                <input type="text" name="job" class="form-control" value="{{ old('job') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Photo -->
                    <div class="form-group">
                        <label for="photo">Guarantor Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                    <!-- National ID Photo -->
                    <div class="form-group">
                        <label for="national_id_photo">National ID Photo</label>
                        <input type="file" name="national_id_photo" class="form-control" accept="image/*">
                    </div>

                    <!-- Added By -->
                    <div class="form-group">
                        <label for="added_by">Added By</label>
                        <select name="added_by" class="form-control">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('added_by') == $user->id ? 'selected' : '' }}>
                                    {{ $user->f_name . ' ' . $user->l_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="reset" class="btn btn-secondary me-3">Reset</button>
                        <button type="submit" class="btn btn-primary">Add Guarantor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Floating Button to go to Client Profile -->
<a href="{{ route('admin.clients.profile', $client->id) }}" class="btn btn-primary rounded-circle shadow-lg" style="position: fixed; bottom: 20px; right: 20px; width: 60px; height: 60px; display: flex; justify-content: center; align-items: center; font-size: 24px;">
    <i class="fa fa-user"></i>
</a>
@endsection
