# Agent Instructions

When explaining concepts to users, use the appropriate style:

## layman
Explain business logic as if the user is a non-technical person. Use simple analogies, everyday examples, and avoid technical jargon. Focus on "what happens" and "why it matters" in plain language.

## expert
Explain default way - technical details, code-level understanding, database implications, API contracts, and precise behavior. Assume user understands the system architecture and technical terminology.

---

## Testing APIs

When testing Laravel APIs, use PHP scripts instead of curl:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'admin@eschool.pk')->first();

$controller = new \App\Http\Controllers\YourController();
$request = new \Illuminate\Http\Request();
$request->replace(['param1' => 'value1']);
$request->setUserResolver(fn() => $user);

$response = $controller->yourMethod($request);
$data = json_decode($response->getContent(), true);

print_r($data);
```

Clean up test files after use: `rm test_*.php`