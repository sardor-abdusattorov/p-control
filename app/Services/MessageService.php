<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Contract;
use App\Models\Message;
use App\Models\Chat;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MessageService
{
    /**
     * Отправить сообщение в чат для указанного типа сущности.
     *
     * @param Request $request
     * @param int $entityId
     * @param string $entityType ('application' или 'contract')
     * @return Message
     */
    public function sendMessage(Request $request, $entityId, $entityType)
    {
        // Валидация данных
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'files.*' => 'file|mimes:jpg,png,pdf,docx|max:2048',
        ]);

        // Определяем сущность (заявка или контракт)
        if ($entityType === 'application') {
            $entity = Application::findOrFail($entityId);
        } elseif ($entityType === 'contract') {
            $entity = Contract::findOrFail($entityId);
        } else {
            throw new \InvalidArgumentException('Invalid entity type');
        }

        // Находим или создаем чат
        $chat = $entity->chats()->firstOrCreate([
            'sender_id' => auth()->id(),
            'receiver_id' => $entity->user_id, // Например, отправляем владельцу
        ]);

        // Создаем сообщение
        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        // Обработка файлов с использованием Spatie Media Library
        if ($request->hasFile('files')) {
            $this->addFiles($message, $request->file('files'));
        }

        return $message;
    }

    /**
     * Добавить файлы в медиа библиотеку.
     *
     * @param Message $message
     * @param array $files
     */
    public function addFiles(Message $message, $files)
    {
        foreach ($files as $file) {
            $message->addMedia($file)  // Добавляем файл в медиа библиотеку
            ->toMediaCollection('chatFiles');  // Используем коллекцию 'files'
        }
    }
}
