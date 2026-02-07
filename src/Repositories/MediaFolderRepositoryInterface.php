<?php

declare(strict_types=1);

namespace Molitor\Media\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Media\Models\MediaFolder;

interface MediaFolderRepositoryInterface
{
    public function getAll(): Collection;

    public function getById(int $id): ?MediaFolder;

    public function getByParentId(?int $parentId): Collection;

    public function create(array $data): MediaFolder;

    public function update(MediaFolder $folder, array $data): MediaFolder;

    public function delete(MediaFolder $folder): bool;
}
