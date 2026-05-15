<?php

namespace Molitor\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Molitor\Media\Http\Requests\StoreMediaFolderRequest;
use Molitor\Media\Http\Requests\UpdateMediaFolderRequest;
use Molitor\Media\Http\Resources\MediaFolderResource;
use Molitor\Media\Repositories\MediaFolderRepositoryInterface;

class MediaFolderApiController extends Controller
{
    public function __construct(
        protected MediaFolderRepositoryInterface $mediaFolderRepository
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $parentId = $request->query('parent_id');

        if ($parentId !== null) {
            $folders = $this->mediaFolderRepository->getByParentId((int) $parentId);
        } else {
            $folders = $this->mediaFolderRepository->getAll();
        }

        return response()->json([
            'data' => MediaFolderResource::collection($folders),
        ]);
    }

    public function tree(): JsonResponse
    {
        $tree = $this->mediaFolderRepository->getTree();

        return response()->json([
            'data' => MediaFolderResource::collection($tree),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $folder = $this->mediaFolderRepository->getById($id);

        if (! $folder) {
            return response()->json(['message' => 'Folder not found'], 404);
        }

        return response()->json([
            'data' => new MediaFolderResource($folder),
        ]);
    }

    public function store(StoreMediaFolderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $folder = $this->mediaFolderRepository->create($validated);

        return response()->json([
            'data' => new MediaFolderResource($folder),
        ], 201);
    }

    public function update(UpdateMediaFolderRequest $request, int $id): JsonResponse
    {
        $folder = $this->mediaFolderRepository->getById($id);

        if (! $folder) {
            return response()->json(['message' => 'Folder not found'], 404);
        }

        $validated = $request->validated();
        $folder = $this->mediaFolderRepository->update($folder, $validated);

        return response()->json([
            'data' => new MediaFolderResource($folder),
        ]);
    }

    public function move(Request $request, int $id): JsonResponse
    {
        $folder = $this->mediaFolderRepository->getById($id);
        if (! $folder) {
            return response()->json(['message' => 'Folder not found'], 404);
        }

        $parentId = $request->input('parent_id');
        $moved = $this->mediaFolderRepository->move($folder, $parentId !== null ? (int) $parentId : null);

        return response()->json([
            'data' => new MediaFolderResource($moved),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $folder = $this->mediaFolderRepository->getById($id);

        if (! $folder) {
            return response()->json(['message' => 'Folder not found'], 404);
        }

        $this->mediaFolderRepository->delete($folder);

        return response()->json(null, 204);
    }
}


