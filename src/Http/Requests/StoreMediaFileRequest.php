<?php

namespace Molitor\Media\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required_without:url', 'file', 'max:10240'], // Max 10MB
            'url' => ['required_without:file', 'url', 'max:255'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
