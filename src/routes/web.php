<?php

use Illuminate\Support\Facades\Route;
use Molitor\Media\Http\Controllers\MediaFileController;

// Public media file download (no auth required)
Route::get('downloads/{id}/{filename}', [MediaFileController::class, 'download'])
    ->name('media.files.download');

