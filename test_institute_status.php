<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get an institute from the database to test with
$institute = \App\Models\Institute::first();

if (!$institute) {
    echo "No institutes found in database. Please run institute seeder first.\n";
    exit(1);
}

echo "Testing institute status endpoint with institute ID: {$institute->id}\n";
echo "Institute status from DB: {$institute->status}\n\n";

// Make request to the endpoint
$uri = '/api/institutes/' . $institute->id . '/status';

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = $uri;

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Handle the request
$response = $app->handle(
    Symfony\Component\HttpFoundation\Request::createFromGlobals()
);

// Send response
$response->send();

// Clean up
$app->terminate(
    Symfony\Component\HttpFoundation\Request::createFromGlobals(),
    $response
);