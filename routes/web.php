<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/links-storage', function (Request $request) {
    // for php artisan storage:link
    Artisan::call('storage:link');

    return response()->json([
        'success' => true,
        'message' => 'Hello World',
    ]);
});

Route::get('/ops/run-language-maintenance', function (Request $request) {


    $results = [];

    foreach (['config:clear', 'cache:clear', 'route:clear', 'view:clear', 'storage:link', 'optimize:clear'] as $command) {
        Artisan::call($command);
        $results[$command] = trim(Artisan::output()) ?: 'OK';
    }

    // Run migrations for any new tables (e.g. otp_verifications).
    // Artisan::call('migrate', ['--force' => true]);
    // $results['migrate'] = trim(Artisan::output()) ?: 'OK';

    $composerPath = base_path('composer.phar');
    $composer = is_file($composerPath)
        ? PHP_BINARY . ' ' . escapeshellarg($composerPath)
        : 'composer';

    $dumpAutoload = shell_exec('cd ' . escapeshellarg(base_path()) . ' && ' . $composer . ' dump-autoload -o 2>&1');
    $results['composer dump-autoload'] = trim((string) $dumpAutoload) ?: 'OK';

    return response()->json([
        'success' => true,
        'results' => $results,
    ]);
});
