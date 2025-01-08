<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Application extends Model implements HasMedia
{
    const STATUS_SEND = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;

    use InteractsWithMedia;
    use HasFactory;

    protected $table = 'application';

    protected $fillable = [
        'title',
        'project_id',
        'user_id',
        'status_id',
    ];

    public function getDocuments()
    {
        return $this->getMedia('documents');
    }

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_SEND, 'label' => __('app.status.send')],
            ['id' => self::STATUS_APPROVED, 'label' => __('app.status.approved')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.status.rejected')],
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }

}
