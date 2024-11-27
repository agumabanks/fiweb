<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can add fines to the client.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Client  $client
     * @return mixed
     */
    public function addFine(User $user, Client $client)
    {
        // Example logic: Only admins can add fines
        return $user->is_admin;
    }

    // ... other policy methods ...
}
