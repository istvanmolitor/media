<?php

namespace Molitor\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateMediaFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'media');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
