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

    $source = base_path('packages/marvel');
    $target = base_path('vendor/marvel/shop');

    if (!is_dir($source)) {
        $results['sync-marvel-package'] = 'packages/marvel not found';
    } elseif (is_link($target)) {
        $results['sync-marvel-package'] = 'skipped (vendor/marvel/shop is a symlink)';
    } else {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $copied = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relative = $iterator->getSubPathname();
            if (preg_match('#^(vendor|node_modules|\.git)(/|$)#', str_replace('\\', '/', $relative))) {
                continue;
            }

            $dest = $target . DIRECTORY_SEPARATOR . $relative;
            if ($item->isDir()) {
                if (!is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
                continue;
            }

            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            copy($item->getPathname(), $dest);
            $copied++;
        }

        $results['sync-marvel-package'] = "copied {$copied} files to vendor/marvel/shop";
    }

    foreach (['config:clear', 'cache:clear', 'route:clear', 'view:clear', 'optimize:clear'] as $command) {
        Artisan::call($command);
        $results[$command] = trim(Artisan::output()) ?: 'OK';
    }

    Artisan::call('migrate', ['--force' => true]);
    $results['migrate'] = trim(Artisan::output()) ?: 'OK';

    $composerPath = base_path('composer.phar');
    $composer = is_file($composerPath)
        ? PHP_BINARY . ' ' . escapeshellarg($composerPath)
        : 'composer';

    $dumpAutoload = shell_exec('cd ' . escapeshellarg(base_path()) . ' && ' . $composer . ' dump-autoload -o 2>&1');
    $results['composer dump-autoload'] = trim((string) $dumpAutoload) ?: 'OK';

    try {
        Artisan::call('db:seed', [
            '--class' => 'Marvel\\Database\\Seeders\\BdLocationSeeder',
            '--force' => true,
        ]);
        $results['bd-location-seed'] = trim(Artisan::output()) ?: 'OK';
    } catch (Throwable $e) {
        $results['bd-location-seed'] = $e->getMessage();
    }

    $controllerPath = $target . '/src/Http/Controllers/LocationController.php';
    $results['location-controller'] = is_file($controllerPath)
        ? 'present at vendor/marvel/shop/src/Http/Controllers/LocationController.php'
        : 'MISSING after sync';

    return response()->json([
        'success' => true,
        'results' => $results,
    ]);
});
