<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class ProductCategory extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $appends = ['image_url'];

    protected $table = 'product_categories';

    protected $fillable = [
        'title',
        'sort',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function getImageUrlAttribute(): string
    {
        $media = $this->getFirstMedia('image');

        if ($media && Storage::disk($media->disk)->exists($media->getPathRelativeToRoot())) {
            return $media->getUrl();
        }

        return asset('images/no_image.png');
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
