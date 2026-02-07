<?php

namespace Molitor\Media\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaFileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'filename' => $this->filename,
            'path' => $this->path,
            'url' => asset('storage/' . $this->path),
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'folder_id' => $this->folder_id,
            'folder' => $this->whenLoaded('folder', fn() => new MediaFolderResource($this->folder)),
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user'),
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
