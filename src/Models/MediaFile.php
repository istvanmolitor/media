<?php

namespace Molitor\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class MediaFile extends Model
{
    protected $fillable = [
        'name',
        'filename',
        'path',
        'mime_type',
        'size',
        'folder_id',
        'user_id',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
