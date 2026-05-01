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
        $extension = $uploadedFile->getClientOriginalExtension();

        $width = null;
        $height = null;
        if (str_starts_with($uploadedFile->getMimeType(), 'image/')) {
            $imageSize = @getimagesize($uploadedFile->getRealPath());
            if ($imageSize) {
                $width = $imageSize[0];
                $height = $imageSize[1];
            }
        }

        $mediaFile = $this->create([
            'name' => pathinfo($filename, PATHINFO_FILENAME),
            'filename' => $filename,
            'path' => 'temporary', // Temporary path
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'width' => $width,
            'height' => $height,
            'folder_id' => $folderId,
            'user_id' => $userId,
        ]);

        $path = $this->updatePath($mediaFile);

        $uploadedFile->storeAs($this->getFolder($mediaFile), basename($path), 'public');

        return $mediaFile;
    }

    public function storeFromUrl(string $url, ?int $folderId = null, ?int $userId = null): MediaFile
    {
        $contents = file_get_contents($url);
        if ($contents === false) {
            throw new \RuntimeException("Could not fetch file from URL: {$url}");
        }

        $originalFilename = basename(parse_url($url, PHP_URL_PATH) ?: 'file');
        if (! str_contains($originalFilename, '.')) {
            $originalFilename .= '.bin'; // Fallback extension if not present
        }
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($contents);

        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            $imageSize = @getimagesizefromstring($contents);
            if ($imageSize) {
                $width = $imageSize[0];
                $height = $imageSize[1];
            }
        }

        $mediaFile = $this->create([
            'name' => pathinfo($originalFilename, PATHINFO_FILENAME),
            'filename' => $originalFilename,
            'path' => 'temporary',
            'mime_type' => $mimeType,
            'size' => strlen($contents),
            'width' => $width,
            'height' => $height,
            'folder_id' => $folderId,
            'user_id' => $userId,
        ]);

        $path = $this->updatePath($mediaFile);

        Storage::disk('public')->put($path, $contents);

        return $mediaFile;
    }

    public function getExtension(MediaFile $mediaFile): ?string
    {
        return $mediaFile->getExtension();
    }

    public function getFolder(MediaFile $mediaFile): string
    {
        return 'media/'.(int) floor($mediaFile->id / 1000);
    }

    private function updatePath(MediaFile $mediaFile): string
    {
        $extension = $this->getExtension($mediaFile);

        $folder = (int) floor($mediaFile->id / 1000);
        $newFilename = $mediaFile->id.($extension ? '.'.$extension : '');
        $path = $this->getFolder($mediaFile).'/'.$newFilename;

        $this->update($mediaFile, ['path' => $path]);

        return $path;
    }
}
