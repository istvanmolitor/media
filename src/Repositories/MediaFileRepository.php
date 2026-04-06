<?php

declare(strict_types=1);

namespace Molitor\Media\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Molitor\Media\Models\MediaFile;

class MediaFileRepository implements MediaFileRepositoryInterface
{
    public function __construct(
        protected MediaFile $file
    ) {}

    public function getAll(): Collection
    {
        return $this->file->with(['folder', 'user'])->orderBy('created_at', 'desc')->get();
    }

    public function getById(int $id): ?MediaFile
    {
        return $this->file->with(['folder', 'user'])->find($id);
    }

    public function getByFolderId(?int $folderId): Collection
    {
        return $this->file
            ->with(['folder', 'user'])
            ->where('folder_id', $folderId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create(array $data): MediaFile
    {
        return $this->file->create($data);
    }

    public function update(MediaFile $file, array $data): MediaFile
    {
        $file->update($data);

        return $file->fresh();
    }

    public function delete(MediaFile $file): bool
    {
        // Delete the physical file
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        return $file->delete();
    }

    public function store(UploadedFile $uploadedFile, ?int $folderId = null, ?int $userId = null): MediaFile
    {
        $filename = $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->store('media', 'public');

        return $this->create([
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'folder_id' => $folderId,
            'user_id' => $userId,
        ]);
    }

    public function storeFromUrl(string $url, ?int $folderId = null, ?int $userId = null): MediaFile
    {
        $contents = file_get_contents($url);
        if ($contents === false) {
            throw new \RuntimeException("Could not fetch file from URL: {$url}");
        }

        $filename = basename(parse_url($url, PHP_URL_PATH) ?: 'file');
        if (! str_contains($filename, '.')) {
            $filename .= '.bin'; // Fallback extension if not present
        }

        $path = 'media/'.\Illuminate\Support\Str::random(40).'.'.pathinfo($filename, PATHINFO_EXTENSION);
        Storage::disk('public')->put($path, $contents);

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($contents);

        return $this->create([
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $mimeType,
            'size' => strlen($contents),
            'folder_id' => $folderId,
            'user_id' => $userId,
        ]);
    }
}
