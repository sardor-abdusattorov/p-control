<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'receiver_id',
        'name',
        'model_type',
        'model_id',
    ];

    /**
     * Получить сообщения для чата.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Получить пользователя (инициатора) чата.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить получателя чата.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Получить модель, с которой связан чат (например, заказ или другой объект).
     */
    public function model()
    {
        return $this->morphTo();
    }
}
