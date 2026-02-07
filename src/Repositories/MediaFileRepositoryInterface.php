<?php

declare(strict_types=1);

namespace Molitor\Media\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Molitor\Media\Models\MediaFile;

interface MediaFileRepositoryInterface
{
    public function getAll(): Collection;

    public function getById(int $id): ?MediaFile;

    public function getByFolderId(?int $folderId): Collection;

    public function create(array $data): MediaFile;

    public function update(MediaFile $file, array $data): MediaFile;

    public function delete(MediaFile $file): bool;

    public function store(UploadedFile $uploadedFile, ?int $folderId = null, ?int $userId = null): MediaFile;
}
