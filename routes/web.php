<?php

use App\Http\Controllers\DocumentSubmissionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'show'])->name('home');

Route::post('/enquiries', [HomeController::class, 'storeEnquiry'])
    ->middleware('throttle:5,1')
    ->name('enquiries.store');

Route::post('/documents', [DocumentSubmissionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('documents.store');

// Staff only: download a file sent through an upload form.
Route::get('/staff/submissions/{submission}/files/{index}', [DocumentSubmissionController::class, 'download'])
    ->middleware('auth')
    ->whereNumber('index')
    ->name('documents.download');

// Inner pages managed under Pages in the admin. Keep this route last.
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '(?!(?:admin|up|livewire|storage|enquiries|documents|staff)$)[a-z0-9]+(?:-[a-z0-9]+)*')
    ->name('page');
