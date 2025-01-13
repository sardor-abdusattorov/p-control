<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::get('/setLang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return back();
})->name('setlang');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/positions', [ProfileController::class, 'getPositions'])->name('profile.getPositions');

    Route::resource('/application', ApplicationController::class)->except(['update']);
    Route::post('/application/destroy-bulk', [ApplicationController::class, 'destroyBulk'])->name('application.destroy-bulk');
    Route::post('/application/{application}', [ApplicationController::class, 'update'])->name('application.update');
    Route::get('/application/{application}/chat', [ApplicationController::class, 'chat'])
        ->name('application.chat');
    Route::post('/application/{application}/send-message', [ApplicationController::class, 'sendMessage'])
        ->name('application.send-message');
    Route::get('/application/chat-messages/{chat_id}', [ApplicationController::class, 'getMessages'])
        ->name('application.get-messages');
    Route::get('/application/{application}/get-all-chats', [ApplicationController::class, 'getAllChats'])
        ->name('application.get-all-chats');

    Route::resource('status', StatusController::class);
    Route::post('/status/destroy-bulk', [StatusController::class, 'destroyBulk'])->name('status.destroy-bulk');
    Route::resource('currency', CurrencyController::class);
    Route::post('/currency/destroy-bulk', [CurrencyController::class, 'destroyBulk'])->name('currency.destroy-bulk');
    Route::resource('departments', DepartmentsController::class);
    Route::post('/departments/destroy-bulk', [DepartmentsController::class, 'destroyBulk'])->name('departments.destroy-bulk');
    Route::resource('projects', ProjectsController::class);
    Route::get('/projects/{project}/related-contracts', [ProjectsController::class, 'relatedContracts'])
        ->name('projects.related-contracts');
    Route::post('/projects/destroy-bulk', [ProjectsController::class, 'destroyBulk'])->name('projects.destroy-bulk');
    Route::resource('contract', ContractController::class)->except(['update']);
    Route::post('/contract/destroy-bulk', [ContractController::class, 'destroyBulk'])->name('contract.destroy-bulk');
    Route::post('/contract/{contract}', [ContractController::class, 'update'])->name('contract.update');
    Route::resource('positions', PositionsController::class);
    Route::post('/positions/destroy-bulk', [PositionsController::class, 'destroyBulk'])->name('positions.destroy-bulk');
    Route::resource('task', TaskController::class)->except(['update']);
    Route::post('/task/destroy-bulk', [TaskController::class, 'destroyBulk'])->name('task.destroy-bulk');
    Route::post('/task/{task}', [TaskController::class, 'update'])->name('task.update');
    Route::post('/task/{task}/start', [TaskController::class, 'start'])->name('task.start');
    Route::post('/task/{task}/complete', [TaskController::class, 'complete'])->name('task.complete');
    Route::resource('/user', UserController::class)->except(['update']);
    Route::post('/user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::post('/user/destroy-bulk', [UserController::class, 'destroyBulk'])->name('user.destroy-bulk');
    Route::resource('/role', RoleController::class)->except('create', 'show', 'edit');
    Route::post('/role/destroy-bulk', [RoleController::class, 'destroyBulk'])->name('role.destroy-bulk');
    Route::resource('/permission', PermissionController::class)->except('create', 'show', 'edit');
    Route::post('/permission/destroy-bulk', [PermissionController::class, 'destroyBulk'])->name('permission.destroy-bulk');
    Route::post('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
});

require __DIR__.'/auth.php';
