<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Approvals extends Model
{
    use HasFactory;

    protected $table = 'approvals';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'user_id',
        'approved',
        'approved_at',
    ];

    protected $casts = [
        'approved' => 'boolean',
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
}
