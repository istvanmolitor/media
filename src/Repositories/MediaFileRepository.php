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
}
