<?php

use Illuminate\Support\Facades\Route;
use Molitor\Media\Http\Controllers\MediaFileController;
use Molitor\Media\Http\Controllers\MediaFolderController;

// Public media file download (no auth required)
Route::get('media/files/{id}/download', [MediaFileController::class, 'download'])
    ->name('media.files.download');

Route::prefix('media')
    ->middleware(['auth:sanctum'])
    ->name('media.')
    ->group(function () {
        // Media Folders
        Route::get('folders/tree', [MediaFolderController::class, 'tree'])->name('folders.tree');
        Route::patch('folders/{id}/move', [MediaFolderController::class, 'move'])->name('folders.move');
        Route::resource('folders', MediaFolderController::class);

        // Media Files
        Route::patch('files/{id}/move', [MediaFileController::class, 'move'])->name('files.move');
        Route::resource('files', MediaFileController::class);
    });
