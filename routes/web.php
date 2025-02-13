<?php

use App\Http\Controllers\{
    ActivityLogController,
    ApplicationController,
    ContractController,
    CurrencyController,
    DepartmentsController,
    HomeController,
    NotificationController,
    PermissionController,
    PositionsController,
    ProfileController,
    ProjectsController,
    RoleController,
    StatusController,
    TaskController,
    UserController
};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

// Главная страница
Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Установка языка
Route::get('/setLang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return back();
})->name('setlang');

// Группа маршрутов с аутентификацией
Route::middleware(['auth', 'verified', 'check.status'])->group(function () {

    // Профиль
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::post('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/positions', [ProfileController::class, 'getPositions'])->name('getPositions');
    });

    // Заявки (Applications)
    Route::resource('application', ApplicationController::class)->except(['update']);

    Route::post('application/destroy-bulk', [ApplicationController::class, 'destroyBulk'])->name('application.destroy-bulk');

    Route::post('/application/{application}/remove-approver', [ApplicationController::class, 'removeApprover'])
        ->name('application.remove-approver');
    Route::put('/application/{application}/update-approvers', [ApplicationController::class, 'updateApprovers'])->name('application.update-approvers');

    Route::post('/application/{application}', [ApplicationController::class, 'update'])->name('application.update');

    Route::prefix('application/{application}')->name('application.')->group(function () {
        Route::get('/chat', [ApplicationController::class, 'chat'])->name('chat');
        Route::post('/send-message', [ApplicationController::class, 'sendMessage'])->name('send-message');
        Route::get('/chat-messages/{chat_id}', [ApplicationController::class, 'getMessages'])->name('get-messages');
        Route::get('/get-all-chats', [ApplicationController::class, 'getAllChats'])->name('get-all-chats');
        Route::post('/approve', [ApplicationController::class, 'confirmApplication'])->name('approve');
    });

    // Статусы
    Route::resource('status', StatusController::class);
    Route::post('/status/destroy-bulk', [StatusController::class, 'destroyBulk'])->name('status.destroy-bulk');

    // Валюты
    Route::resource('currency', CurrencyController::class);
    Route::post('/currency/destroy-bulk', [CurrencyController::class, 'destroyBulk'])->name('currency.destroy-bulk');

    // Отделы
    Route::resource('departments', DepartmentsController::class);
    Route::post('/departments/destroy-bulk', [DepartmentsController::class, 'destroyBulk'])->name('departments.destroy-bulk');

    // Проекты
    Route::resource('projects', ProjectsController::class);
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        Route::get('/related-contracts', [ProjectsController::class, 'relatedContracts'])->name('related-contracts');
    });
    Route::post('/projects/destroy-bulk', [ProjectsController::class, 'destroyBulk'])->name('projects.destroy-bulk');

    // Контракты
    Route::resource('contract', ContractController::class)->except(['update']);
    Route::post('/contract/destroy-bulk', [ContractController::class, 'destroyBulk'])->name('contract.destroy-bulk'); // Вынесено за пределы группы с параметром
    Route::post('/contract/{contract}/remove-approver', [ContractController::class, 'removeApprover'])
        ->name('contract.remove-approver');
    Route::put('/contract/{contract}/update-approvers', [ContractController::class, 'updateApprovers'])->name('contract.update-approvers');

    Route::get('/contract/chat-messages/{chat_id}', [ContractController::class, 'getMessages'])->name('contract.get-messages');

    Route::prefix('contract/{contract}')->name('contract.')->group(function () {
        Route::post('/update', [ContractController::class, 'update'])->name('update');
        Route::get('/chat', [ContractController::class, 'chat'])->name('chat');
        Route::post('/send-message', [ContractController::class, 'sendMessage'])->name('send-message');
        Route::get('/get-all-chats', [ContractController::class, 'getAllChats'])->name('get-all-chats');
        Route::post('/approve', [ContractController::class, 'confirmContract'])->name('approve');
    });

    // Должности
    Route::resource('positions', PositionsController::class);
    Route::post('/positions/destroy-bulk', [PositionsController::class, 'destroyBulk'])->name('positions.destroy-bulk');

    // Задачи
    Route::resource('task', TaskController::class)->except(['update']);
    Route::post('task/destroy-bulk', [TaskController::class, 'destroyBulk'])->name('task.destroy-bulk');
    Route::prefix('task/{task}')->name('task.')->group(function () {
        Route::post('/update', [TaskController::class, 'update'])->name('update');
        Route::post('/start', [TaskController::class, 'start'])->name('start');
        Route::post('/complete', [TaskController::class, 'complete'])->name('complete');
    });

    // Пользователи
    Route::resource('user', UserController::class)->except(['update']);
    Route::post('user/destroy-bulk', [UserController::class, 'destroyBulk'])->name('user.destroy-bulk');
    Route::prefix('user/{user}')->name('user.')->group(function () {
        Route::post('/update', [UserController::class, 'update'])->name('update');
    });

    // Роли
    Route::resource('role', RoleController::class)->except(['create', 'show', 'edit']);
    Route::post('/role/destroy-bulk', [RoleController::class, 'destroyBulk'])->name('role.destroy-bulk');

    // Права доступа
    Route::resource('permission', PermissionController::class)->except(['create', 'show', 'edit']);
    Route::post('/permission/destroy-bulk', [PermissionController::class, 'destroyBulk'])->name('permission.destroy-bulk');

    // Уведомления
    Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);

    // Логи
    Route::resource('logs', ActivityLogController::class);

});

// Аутентификация
require __DIR__.'/auth.php';
