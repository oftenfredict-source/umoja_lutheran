<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = \App\Models\Staff::where('role', 'head_chef')->first();

$request = Illuminate\Http\Request::create('/stock-requests/create', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});
\Illuminate\Support\Facades\Auth::guard('staff')->login($user);

$response = $kernel->handle($request);
$content = $response->getContent();
file_put_contents('rendered_create.html', $content);
echo "Rendered successfully. Line count: " . count(explode("\n", $content));
