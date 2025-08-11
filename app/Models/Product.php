<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $table = 'products';

    protected $fillable = [
        'title',
        'description',
        'serial_number',
        'inventory_number',
        'parameters',
        'category_id',
        'brand_id',
        'user_id',
        'sort',
        'status',

    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_ACTIVE, 'label' => __('app.status.active')],
            ['id' => self::STATUS_INACTIVE, 'label' => __('app.status.disable')],
        ];
    }

}
