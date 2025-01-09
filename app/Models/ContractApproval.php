<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractApproval extends Model
{
    use HasFactory;
    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = -1;

    protected $table = 'contract_approvals';

    protected $fillable = [
        'user_id',
        'contract_id',
        'status'
    ];

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_NEW, 'label' => __('app.label.active')],
            ['id' => self::STATUS_IN_PROGRESS, 'label' => __('app.label.in_progress')],
            ['id' => self::STATUS_APPROVED, 'label' => __('app.label.approved')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.label.rejected')],
        ];
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
