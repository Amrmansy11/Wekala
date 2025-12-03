<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::middleware('auth:admin-api')->get('/', function () {
   return response()->json(['message' => 'Welcome to Wekala API!']);
});

Route::post('/deploy', function (Request $request) {
    $signature = $request->header('X-Hub-Signature-256');

    if (empty($signature)) {
        return response()->json(['message' => 'No signature provided!'], 403);
    }

    $secret = env('DEPLOY_SECRET');
    $payload = $request->getContent();

    if (!hash_equals($signature, 'sha256=' . hash_hmac('sha256', $payload, $secret))) {
        return response()->json(['message' => 'Invalid signature!'], 403);
    }

    $data = $request->all();

    if (!isset($data['ref']) || $data['ref'] !== 'refs/heads/main') {
        return response()->noContent();
    }

    $output = [];
    $exitCode = 0;
    exec('git pull origin main 2>&1', $output, $exitCode);

    if ($exitCode !== 0) {
        Log::error('Deployment failed', $output);
        return response()->noContent();
    }

    return response()->noContent();
});
