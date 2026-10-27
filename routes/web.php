<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/ready', function () {
    $checks = [
        'database' => false,
        'redis' => false,
    ];

    try {
        DB::select('SELECT 1');
        $checks['database'] = true;
    } catch (\Throwable) {
    }

    try {
        if (config('database.redis.default.host')) {
            $redis = Redis::connection();
            $redis->ping();
            $checks['redis'] = true;
        }
    } catch (\Throwable) {
    }

    $healthy = $checks['database'];
    $status = $healthy ? 200 : 503;

    return response()->json([
        'status' => $healthy ? 'ok' : 'degraded',
        'checks' => $checks,
        'timestamp' => now()->toIso8601String(),
    ], $status);
});

require __DIR__.'/settings.php';
