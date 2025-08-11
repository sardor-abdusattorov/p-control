<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProductBrand extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $appends = ['image_url'];

    protected $table = 'product_brands';

    protected $fillable = [
        'title',
        'sort',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_brand')->singleFile();
    }

    public function getImageUrlAttribute(): string
    {
        $media = $this->getFirstMedia('product_brand');

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
