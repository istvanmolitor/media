<?php

namespace Molitor\Media\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Molitor\Media\Http\Requests\StoreMediaFolderRequest;
use Molitor\Media\Http\Requests\UpdateMediaFolderRequest;
use Molitor\Media\Repositories\MediaFolderRepositoryInterface;
use Molitor\Media\Http\Resources\MediaFolderResource;
use OpenApi\Attributes as OA;

class MediaFolderController extends Controller
{
    public function __construct(
        protected MediaFolderRepositoryInterface $mediaFolderRepository
    ) {}

    #[OA\Get(
        path: "/api/media/folders",
        summary: "Get all media folders",
        tags: ["Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "parent_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function index(): JsonResponse
    {
        $parentId = request()->query('parent_id');

        if ($parentId !== null) {
            $folders = $this->mediaFolderRepository->getByParentId((int)$parentId);
        } else {
            $folders = $this->mediaFolderRepository->getAll();
        }

        return response()->json([
            'data' => MediaFolderResource::collection($folders)
        ]);
    }

    #[OA\Get(
        path: "/api/media/folders/{id}",
        summary: "Get a media folder by ID",
        tags: ["Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $folder = $this->mediaFolderRepository->getById($id);

        if (!$folder) {
            return response()->json(['message' => 'Folder not found'], 404);
        }

        return response()->json([
            'data' => new MediaFolderResource($folder)
        ]);
    }

    #[OA\Post(
        path: "/api/media/folders",
        summary: "Create a new media folder",
        tags: ["Media"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreMediaFolderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $folder = $this->mediaFolderRepository->create($validated);

        return response()->json([
            'data' => new MediaFolderResource($folder)
        ], 201);
    }

    #[OA\Put(
        path: "/api/media/folders/{id}",
        summary: "Update a media folder",
        tags: ["Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function update(UpdateMediaFolderRequest $request, int $id): JsonResponse
    {
        $folder = $this->mediaFolderRepository->getById($id);

        if (!$folder) {
            return response()->json(['message' => 'Folder not found'], 404);
        }

        $validated = $request->validated();
        $folder = $this->mediaFolderRepository->update($folder, $validated);

        return response()->json([
            'data' => new MediaFolderResource($folder)
        ]);
    }

    #[OA\Delete(
        path: "/api/media/folders/{id}",
        summary: "Delete a media folder",
        tags: ["Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 204, description: "No content"),
            new OA\Response(response: 404, description: "Not found")
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $folder = $this->mediaFolderRepository->getById($id);

        if (!$folder) {
            return response()->json(['message' => 'Folder not found'], 404);
        }

        $this->mediaFolderRepository->delete($folder);

        return response()->json(null, 204);
    }
}
