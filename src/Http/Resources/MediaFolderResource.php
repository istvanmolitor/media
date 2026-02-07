<?php

namespace Molitor\Media\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MediaFolderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn() => new MediaFolderResource($this->parent)),
            'path' => $this->path,
            'children' => $this->whenLoaded('children', fn() => MediaFolderResource::collection($this->children)),
            'files' => $this->whenLoaded('files', fn() => MediaFileResource::collection($this->files)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
