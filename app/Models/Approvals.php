<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approvals extends Model
{
    use HasFactory;

    const STATUS_NEW      = 1;
    const STATUS_PENDING  = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = -1;
    const STATUS_INVALIDATED  = -2;

    protected $table = 'approvals';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'user_id',
        'approval_order',
        'approved',
        'reason',
        'approved_at',
    ];

    protected $casts = [
        'approved' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_NEW,      'label' => __('app.status.new')],
            ['id' => self::STATUS_PENDING,  'label' => __('app.status.pending')],
            ['id' => self::STATUS_APPROVED, 'label' => __('app.status.approved')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.status.rejected')],
        ];
    }

    public function scopeValid($query)
    {
        return $query->where('approved', '!=', self::STATUS_INVALIDATED);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('approved', [self::STATUS_INVALIDATED, self::STATUS_NEW]);
    }

    public static function getApprovalOrder(int $departmentId): int
    {
        return match($departmentId) {
            8 => 1,
            7 => 2,
            default => 1,
        };
    }
}
