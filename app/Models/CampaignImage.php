<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CampaignImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'uploader_user_id',
        'image_path',
        'original_filename',
        'caption',
    ];

    /**
     * Get the campaign that this image belongs to.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the user who uploaded this image.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_user_id');
    }

    /**
     * Get the public URL of the image.
     *
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return Storage::disk('public')->url($this->image_path);
        }
        // Optionally return a default/placeholder image URL if needed
        return ''; // Or asset('images/placeholder.jpg')
    }
}
