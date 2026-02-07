<?php

declare(strict_types=1);

namespace Molitor\Media\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Molitor\Media\Models\MediaFolder;

class MediaFolderRepository implements MediaFolderRepositoryInterface
{
    public function __construct(
        protected MediaFolder $folder
    ) {}

    public function getAll(): Collection
    {
        return $this->folder->with('parent')->orderBy('name')->get();
    }

    public function getById(int $id): ?MediaFolder
    {
        return $this->folder->with(['parent', 'children', 'files'])->find($id);
    }

    public function getByParentId(?int $parentId): Collection
    {
        return $this->folder
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): MediaFolder
    {
        return $this->folder->create($data);
    }

    public function update(MediaFolder $folder, array $data): MediaFolder
    {
        $folder->update($data);
        return $folder->fresh();
    }

    public function delete(MediaFolder $folder): bool
    {
        return $folder->delete();
    }
}
