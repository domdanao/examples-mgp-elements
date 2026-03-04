<?php

use App\Http\Controllers\GuideController;
use App\Http\Middleware\AllowIframeEmbedding;
use Illuminate\Support\Facades\Route;

// Guide site routes
Route::get('/', [GuideController::class, 'index'])->name('guide.index');
Route::get('/docs', [GuideController::class, 'docs'])->name('guide.docs');
Route::get('/examples', [GuideController::class, 'examples'])->name('guide.examples');

// Serve example files with iframe embedding allowed (for previews)
Route::middleware(AllowIframeEmbedding::class)->group(function () {
    Route::get('/examples/{path}', function ($path) {
        $file = resource_path("examples/{$path}");

        if (! file_exists($file) || ! is_file($file)) {
            abort(404);
        }

        $mimeType = match (pathinfo($file, PATHINFO_EXTENSION)) {
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };

        return response(file_get_contents($file))
            ->header('Content-Type', $mimeType);
    })->where('path', '.*');
});
