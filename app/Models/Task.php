<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Task extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_REJECTED = 4;

    const PRIORITY_LOW = 1;
    const PRIORITY_MEDIUM = 2;
    const PRIORITY_HIGH = 3;

    protected $table = 'tasks';

    protected $fillable = [
        'name',
        'status',
        'priority',
        'due_date',
        'user_id',
        'description',
        'assigned_user'
    ];

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_NEW, 'label' => __('app.status.new')],
            ['id' => self::STATUS_IN_PROGRESS, 'label' => __('app.status.in_progress')],
            ['id' => self::STATUS_COMPLETED, 'label' => __('app.status.completed')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.status.rejected')],
        ];
    }

    public static function getPriorities(): array
    {
        return [
            ['id' => self::PRIORITY_LOW, 'label' => __('app.priority.low')],
            ['id' => self::PRIORITY_MEDIUM, 'label' => __('app.priority.medium')],
            ['id' => self::PRIORITY_HIGH, 'label' => __('app.priority.high')],
        ];
    }

    public function taskCompletion()
    {
        return $this->hasOne(TaskCompletion::class);
    }

    public function getDueDateAttribute()
    {
        return Carbon::parse($this->attributes['due_date'])->format('d-m-Y H:i');
    }

    public function getCreatedAtAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-m-Y H:i');
    }

    public function getUpdatedAtAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->format('d-m-Y H:i');
    }
}
