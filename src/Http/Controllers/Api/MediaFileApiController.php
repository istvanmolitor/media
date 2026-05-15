<?php

namespace Molitor\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Molitor\Media\Http\Requests\StoreMediaFileRequest;
use Molitor\Media\Http\Requests\UpdateMediaFileRequest;
use Molitor\Media\Http\Resources\MediaFileResource;
use Molitor\Media\Repositories\MediaFileRepositoryInterface;

class MediaFileApiController extends Controller
{
    public function __construct(
        protected MediaFileRepositoryInterface $mediaFileRepository
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $folderId = $request->query('folder_id');

        if ($folderId !== null) {
            $files = $this->mediaFileRepository->getByFolderId((int) $folderId);
        } else {
            $files = $this->mediaFileRepository->getAll();
        }

        return response()->json([
            'data' => MediaFileResource::collection($files),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $file = $this->mediaFileRepository->getById($id);

        if (! $file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->json([
            'data' => new MediaFileResource($file),
        ]);
    }

    public function store(StoreMediaFileRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = Auth::id();

        if ($request->has('url')) {
            $file = $this->mediaFileRepository->storeFromUrl(
                $validated['url'],
                $validated['folder_id'] ?? null,
                $userId
            );
        } else {
            $file = $this->mediaFileRepository->store(
                $validated['file'],
                $validated['folder_id'] ?? null,
                $userId
            );
        }

        if (isset($validated['description'])) {
            $file = $this->mediaFileRepository->update($file, [
                'description' => $validated['description'],
            ]);
        }

        return response()->json([
            'data' => new MediaFileResource($file),
        ], 201);
    }

    public function update(UpdateMediaFileRequest $request, int $id): JsonResponse
    {
        $file = $this->mediaFileRepository->getById($id);

        if (! $file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $validated = $request->validated();
        $file = $this->mediaFileRepository->update($file, $validated);

        return response()->json([
            'data' => new MediaFileResource($file),
        ]);
    }

    public function move(Request $request, int $id): JsonResponse
    {
        $file = $this->mediaFileRepository->getById($id);
        if (! $file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $folderId = $request->input('folder_id');
        $updated = $this->mediaFileRepository->update($file, ['folder_id' => $folderId !== null ? (int) $folderId : null]);

        return response()->json([
            'data' => new MediaFileResource($updated),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $file = $this->mediaFileRepository->getById($id);

        if (! $file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $this->mediaFileRepository->delete($file);

        return response()->json(null, 204);
    }
}


