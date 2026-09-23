<?php
// ONE-TIME USE — DELETE after running!
// Access: https://api.doinikhisab.online/run-seed.php?secret=seed2026doinikhi

if (($_GET['secret'] ?? '') !== 'seed2026doinikhi') {
    http_response_code(403); die('Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app    = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo '<pre>';
$kernel->call('db:seed', ['--class' => 'DemoDataSeeder', '--force' => true]);
echo $kernel->output();
echo '</pre>';
echo '<strong style="color:green">Done! DELETE this file now.</strong>';
