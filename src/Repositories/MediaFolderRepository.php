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

    public function getTree(): Collection
    {
        // Load all folders and build a nested tree by setting the 'children' relation manually
        /** @var Collection<int, MediaFolder> $all */
        $all = $this->folder->orderBy('name')->get();
        $byId = $all->keyBy('id');
        $roots = collect();

        // Initialize empty children collections
        foreach ($all as $f) {
            $f->setRelation('children', collect());
        }

        foreach ($all as $f) {
            if ($f->parent_id && $byId->has($f->parent_id)) {
                /** @var MediaFolder $parent */
                $parent = $byId->get($f->parent_id);
                $children = $parent->getRelation('children');
                $parent->setRelation('children', $children->push($f));
            } else {
                $roots->push($f);
            }
        }

        return $roots;
    }

    public function create(array $data): MediaFolder
    {
        return $this->folder->create($data);
    }

    public function getOrCreateByPath(array $segments): MediaFolder
    {
        $normalizedSegments = $this->normalizePathSegments($segments);

        if ($normalizedSegments === []) {
            throw new \InvalidArgumentException('At least one non-empty path segment is required.');
        }

        $parentId = null;
        $currentPath = '';
        $currentFolder = null;

        foreach ($normalizedSegments as $segment) {
            $currentPath .= '/'.$segment;

            $currentFolder = $this->folder->newQuery()->firstOrCreate(
                [
                    'name' => $segment,
                    'parent_id' => $parentId,
                ],
                [
                    'path' => $currentPath,
                ],
            );

            $parentId = $currentFolder->id;
        }

        return $currentFolder;
    }

    public function update(MediaFolder $folder, array $data): MediaFolder
    {
        $folder->update($data);

        return $folder->fresh();
    }

    public function move(MediaFolder $folder, ?int $newParentId): MediaFolder
    {
        if ($newParentId === $folder->id) {
            return $folder; // no-op invalid self-parenting
        }

        // Prevent cycles: walk up from new parent to root
        if ($newParentId) {
            $current = $this->folder->find($newParentId);
            while ($current) {
                if ($current->id === $folder->id) {
                    // Trying to move under its own descendant -> keep as is
                    return $folder;
                }
                if (! $current->parent_id) {
                    break;
                }
                $current = $this->folder->find($current->parent_id);
            }
        }

        $folder->parent_id = $newParentId;
        $folder->save();

        return $folder->fresh(['parent', 'children']);
    }

    public function delete(MediaFolder $folder): bool
    {
        return $folder->delete();
    }

    /**
     * @param  array<int, string>  $segments
     * @return array<int, string>
     */
    private function normalizePathSegments(array $segments): array
    {
        /** @var array<int, string> $normalizedSegments */
        $normalizedSegments = collect($segments)
            ->filter(fn (mixed $segment): bool => is_string($segment))
            ->map(fn (string $segment): string => trim($segment))
            ->filter(fn (string $segment): bool => $segment !== '')
            ->values()
            ->all();

        return $normalizedSegments;
    }
}
