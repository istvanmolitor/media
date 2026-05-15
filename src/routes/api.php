<?php

use Illuminate\Support\Facades\Route;
use Molitor\Media\Http\Controllers\Api\MediaFileApiController;
use Molitor\Media\Http\Controllers\Api\MediaFolderApiController;


Route::prefix('api/media')
    ->middleware(['auth:sanctum'])
    ->name('media.')
    ->group(function () {
        // Media Folders
        Route::get('folders/tree', [MediaFolderApiController::class, 'tree'])->name('folders.tree');
        Route::patch('folders/{id}/move', [MediaFolderApiController::class, 'move'])->name('folders.move');
        Route::resource('folders', MediaFolderApiController::class);

        // Media Files
        Route::patch('files/{id}/move', [MediaFileApiController::class, 'move'])->name('files.move');
        Route::resource('files', MediaFileApiController::class);
    });
