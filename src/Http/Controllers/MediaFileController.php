<?php

namespace Molitor\Media\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Molitor\Media\Http\Requests\StoreMediaFileRequest;
use Molitor\Media\Http\Requests\UpdateMediaFileRequest;
use Molitor\Media\Repositories\MediaFileRepositoryInterface;
use Molitor\Media\Http\Resources\MediaFileResource;
use OpenApi\Attributes as OA;

class MediaFileController extends Controller
{
    public function __construct(
        protected MediaFileRepositoryInterface $mediaFileRepository
    ) {}

    #[OA\Get(
        path: "/api/media/files",
        summary: "Get all media files",
        tags: ["Media"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(name: "folder_id", in: "query", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Success")
        ]
    )]
    public function index(): JsonResponse
    {
        $folderId = request()->query('folder_id');

        if ($folderId !== null) {
            $files = $this->mediaFileRepository->getByFolderId((int)$folderId);
        } else {
            $files = $this->mediaFileRepository->getAll();
        }

        return response()->json([
            'data' => MediaFileResource::collection($files)
        ]);
    }

    #[OA\Get(
        path: "/api/media/files/{id}",
        summary: "Get a media file by ID",
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
        $file = $this->mediaFileRepository->getById($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->json([
            'data' => new MediaFileResource($file)
        ]);
    }

    #[OA\Post(
        path: "/api/media/files",
        summary: "Upload a media file",
        tags: ["Media"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["file"],
                    properties: [
                        new OA\Property(property: "file", type: "string", format: "binary"),
                        new OA\Property(property: "folder_id", type: "integer"),
                        new OA\Property(property: "description", type: "string")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreMediaFileRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = Auth::id();

        $file = $this->mediaFileRepository->store(
            $validated['file'],
            $validated['folder_id'] ?? null,
            $userId
        );

        if (isset($validated['description'])) {
            $file = $this->mediaFileRepository->update($file, [
                'description' => $validated['description']
            ]);
        }

        return response()->json([
            'data' => new MediaFileResource($file)
        ], 201);
    }

    #[OA\Put(
        path: "/api/media/files/{id}",
        summary: "Update a media file",
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
    public function update(UpdateMediaFileRequest $request, int $id): JsonResponse
    {
        $file = $this->mediaFileRepository->getById($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $validated = $request->validated();
        $file = $this->mediaFileRepository->update($file, $validated);

        return response()->json([
            'data' => new MediaFileResource($file)
        ]);
    }

    #[OA\Delete(
        path: "/api/media/files/{id}",
        summary: "Delete a media file",
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
        $file = $this->mediaFileRepository->getById($id);

        if (!$file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $this->mediaFileRepository->delete($file);

        return response()->json(null, 204);
    }
}
