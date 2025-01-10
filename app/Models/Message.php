<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'chat_id',
        'user_id',
        'text',
        'is_notified',
        'created_date',
    ];

    /**
     * Получить чат, к которому принадлежит сообщение.
     */
    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Получить пользователя, отправившего сообщение.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
