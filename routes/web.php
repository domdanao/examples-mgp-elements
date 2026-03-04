<?php

use App\Http\Controllers\GuideController;
use Illuminate\Support\Facades\Route;

// Guide site routes
Route::get('/', [GuideController::class, 'index'])->name('guide.index');
Route::get('/docs', [GuideController::class, 'docs'])->name('guide.docs');
Route::get('/examples', [GuideController::class, 'examples'])->name('guide.examples');
