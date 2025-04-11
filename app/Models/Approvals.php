<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approvals extends Model
{
    use HasFactory;

    const STATUS_REJECTED = 0;
    const STATUS_PENDING  = 1;
    const STATUS_APPROVED = 2;

    const STATUS_NEW = 3;


    protected $table = 'approvals';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'user_id',
        'approved',
        'reason',
        'approved_at',
    ];

    protected $casts = [
        'approved' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Полиморфная связь (поддерживает контракты и заявки).
     */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Пользователь, который подтвердил.
     */
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
}
