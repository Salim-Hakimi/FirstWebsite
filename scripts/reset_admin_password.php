<?php

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$password = getenv('RESET_ADMIN_PASSWORD');

if (! $password) {
    fwrite(STDERR, "RESET_ADMIN_PASSWORD is required.\n");
    exit(1);
}

$email = getenv('RESET_ADMIN_EMAIL') ?: env('FANOUS_ADMIN_EMAIL');

$user = $email
    ? User::where('email', $email)->first()
    : User::where('role', User::ROLE_ADMIN)->orderBy('id')->first();

if (! $user) {
    fwrite(STDERR, "No admin user found.\n");
    exit(1);
}

$user->forceFill([
    'password' => Hash::make($password),
    'status' => User::STATUS_ACTIVE,
])->save();

echo "Admin password reset for {$user->email}\n";
