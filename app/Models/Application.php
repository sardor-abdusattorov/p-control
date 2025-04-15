<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Application extends Model implements HasMedia
{
    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = -1;
    const STATUS_INVALIDATED = -2;
    const TYPE_REQUEST = 1;
    const TYPE_MEMO = 2;


    use InteractsWithMedia;
    use HasFactory;

    protected $table = 'application';

    protected $fillable = [
        'title',
        'project_id',
        'user_id',
        'status_id',
        'type',
        'currency_id',
    ];

    public function getDocuments()
    {
        return $this->getMedia('documents');
    }

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_NEW, 'label' => __('app.status.new')],
            ['id' => self::STATUS_IN_PROGRESS, 'label' => __('app.status.in_progress')],
            ['id' => self::STATUS_APPROVED, 'label' => __('app.status.approved')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.status.rejected')],
            ['id' => self::STATUS_INVALIDATED, 'label' => __('app.status.invalidated')],
        ];
    }

    public static function getTypes(): array
    {
        return [
            ['id' => self::TYPE_REQUEST, 'label' => __('app.type.request')],
            ['id' => self::TYPE_MEMO, 'label' => __('app.type.memo')],
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
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }

    public function approvals()
    {
        return $this->morphMany(Approvals::class, 'approvable');
    }

}
