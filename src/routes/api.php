<?php

use Illuminate\Support\Facades\Route;
use Molitor\Media\Http\Controllers\MediaFileController;
use Molitor\Media\Http\Controllers\MediaFolderController;

Route::prefix('media')
    ->middleware(['api', 'auth:sanctum'])
    ->name('media.')
    ->group(function () {
        // Media Folders
        Route::resource('folders', MediaFolderController::class);

        // Media Files
        Route::resource('files', MediaFileController::class);
    });
