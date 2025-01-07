<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;
    const STATUS_INACTIVE = 0;

    const STATUS_ACTIVE = 1;

    protected $table = 'currency';

    protected $fillable = [
        'name',
        'short_name',
        'value',
        'status'
    ];

    public static function getStatuses(): array
    {
        return [
            ['value' => self::STATUS_ACTIVE, 'label' => __('app.label.active')],
            ['value' => self::STATUS_INACTIVE, 'label' => __('app.label.inactive')],
        ];
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
