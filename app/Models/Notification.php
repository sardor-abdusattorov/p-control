<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    protected $table = 'notifications';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'receiver_id',
        'model_id',
        'model',
        'read_at',
        'action',
        'is_read'
    ];

    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
}

