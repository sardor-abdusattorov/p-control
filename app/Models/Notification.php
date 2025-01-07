<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    protected $table = 'notifications';

    use HasFactory;

    const TYPE_TASK_ASSIGNED = 1;
    const TYPE_TASK_COMPLETED = 2;

    protected $fillable = [
        'user_id',
        'receiver_id',
        'type',
        'read_at',
        'task_id',
        'is_read'
    ];


    public static function getType(): array
    {
        return [
            ['id' => self::TYPE_TASK_ASSIGNED, 'label' => __('app.label.task_assigned')],
            ['id' => self::TYPE_TASK_COMPLETED, 'label' => __('app.label.task_completed')]
        ];
    }


    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
