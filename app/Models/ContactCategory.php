<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactCategory extends Model
{
    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    protected $fillable = [
        'title',
        'info',
        'status',
    ];

    public function contacts()
    {
        return $this->hasMany(Contact::class, 'category_id');
    }

    public function subcategories()
    {
        return $this->hasMany(ContactSubcategory::class, 'category_id');
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
            ['id' => self::STATUS_DISABLED, 'label' => __('app.status.disable')],
            ['id' => self::STATUS_ACTIVE, 'label' => __('app.status.active')],
        ];
    }
}
