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

    /**
     * Return the full folder tree (roots with nested children)
     */
    public function getTree(): Collection;

    public function create(array $data): MediaFolder;

    public function update(MediaFolder $folder, array $data): MediaFolder;

    /**
     * Move folder under a new parent (can be null for root)
     */
    public function move(MediaFolder $folder, ?int $newParentId): MediaFolder;

    public function delete(MediaFolder $folder): bool;
}
