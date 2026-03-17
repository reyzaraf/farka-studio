<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@farkastudio.id',
    'password' => bcrypt('password')
]);
echo "User admin@farkastudio.id created with password 'password'";
