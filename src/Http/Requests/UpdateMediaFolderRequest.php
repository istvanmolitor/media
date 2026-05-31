<?php

namespace Molitor\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateMediaFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('acl', 'media');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'path' => ['nullable', 'string', 'max:500'],
        ];
    }
}
