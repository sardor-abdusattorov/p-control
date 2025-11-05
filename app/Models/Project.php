<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    const STATUS_NEW = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = -1;


    protected $fillable = [
        'project_number',
        'title',
        'budget_sum',
        'project_year',
        'user_id',
        'status_id',
        'currency_id',
        'deadline',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_NEW, 'label' => __('app.status.new')],
            ['id' => self::STATUS_APPROVED, 'label' => __('app.status.approved')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.status.rejected')],
        ];
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class, 'project_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}
