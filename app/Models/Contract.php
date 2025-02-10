<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contract extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = -1;


    protected $table = 'contracts';

    protected $fillable = [
        'contract_number',
        'project_id',
        'application_id',
        'user_id',
        'status',
        'currency_id',
        'title',
        'budget_sum',
        'deadline',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function approvals()
    {
        return $this->morphMany(Approvals::class, 'approvable');
    }


    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_NEW, 'label' => __('app.status.new')],
            ['id' => self::STATUS_IN_PROGRESS, 'label' => __('app.status.in_progress')],
            ['id' => self::STATUS_APPROVED, 'label' => __('app.status.approved')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.status.rejected')],
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
