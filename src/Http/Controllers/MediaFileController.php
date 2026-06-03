<?php

namespace Molitor\Media\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Molitor\Media\Repositories\MediaFileRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaFileController extends Controller
{
    public function __construct(
        protected MediaFileRepositoryInterface $mediaFileRepository
    ) {}

    public function download(int $id): BinaryFileResponse|JsonResponse
    {
        $file = $this->mediaFileRepository->getById($id);

        if (! $file) {
            return response()->json(['message' => 'File not found'], 404);
        }

        $filePath = storage_path('app/public/'.$file->path);

        if (! file_exists($filePath)) {
            return response()->json(['message' => 'File not found on disk'], 404);
        }

        if (str_starts_with((string) $file->mime_type, 'image/')) {
            return response()->file($filePath, [
                'Content-Type' => (string) $file->mime_type,
                'Content-Disposition' => 'inline; filename="'.$file->filename.'"',
            ]);
        }

        return response()->download($filePath, $file->filename);
    }
}
