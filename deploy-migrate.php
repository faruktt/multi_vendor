<?php
/**
 * ONE-TIME USE: Upload to public/ folder on cPanel, run once, then DELETE immediately.
 * Access via: https://api.yourdomain.com/deploy-migrate.php?secret=CHANGE_THIS_SECRET
 */

$secret = 'CHANGE_THIS_SECRET'; // <-- CHANGE THIS before uploading!

if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    http_response_code(403);
    die('Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo '<pre>';

$commands = [
    ['migrate', ['--force' => true]],
    ['db:seed', ['--force' => true]],
    ['storage:link', []],
    ['config:cache', []],
    ['route:cache', []],
    ['view:cache', []],
];

foreach ($commands as [$cmd, $args]) {
    echo "Running: php artisan $cmd ...\n";
    $status = $kernel->call($cmd, $args);
    echo $kernel->output();
    echo "Exit code: $status\n\n";
}

echo '</pre>';
echo '<strong style="color:red">IMPORTANT: DELETE this file now! (deploy-migrate.php)</strong>';
