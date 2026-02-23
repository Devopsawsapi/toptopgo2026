<?php

use App\Models\User;
use App\Models\Ride;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User private channel
Broadcast::channel('user.{id}', function (User $user, int $id) {
    return $user->id === $id;
});

// Ride channel (for passenger and driver)
Broadcast::channel('ride.{rideId}', function (User $user, int $rideId) {
    $ride = Ride::find($rideId);

    if (!$ride) {
        return false;
    }

    return $user->id === $ride->passenger_id || $user->id === $ride->driver_id;
});

// Available drivers presence channel
Broadcast::channel('drivers.available', function (User $user) {
    if (!$user->isDriver()) {
        return false;
    }

    $profile = $user->driverProfile;

    if (!$profile || !$profile->is_online) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'location' => [
            'latitude' => $profile->current_latitude,
            'longitude' => $profile->current_longitude,
        ],
    ];
});

// Public rides channel (for admin monitoring)
Broadcast::channel('rides', function (User $user) {
    return $user->isAdmin();
});

// Admin dashboard channel
Broadcast::channel('admin.dashboard', function (User $user) {
    return $user->isAdmin();
});
