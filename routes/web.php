<?php

use App\Http\Controllers\{ActivityLogController,
    ApplicationController,
    ContactCategoryController,
    ContactController,
    ContactSubcategoryController,
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
    UserController};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/setLang/{locale}', function ($locale) {
    Session::put('locale', $locale);
    return back();
})->name('setlang');

Route::middleware(['auth', 'verified', 'check.status'])->group(function () {

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::post('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/positions', [ProfileController::class, 'getPositions'])->name('getPositions');
    });

    Route::resource('application', ApplicationController::class)->except(['update']);
    Route::post('application/destroy-bulk', [ApplicationController::class, 'destroyBulk'])->name('application.destroy-bulk');
    Route::post('/application/{application}/remove-approver', [ApplicationController::class, 'removeApprover'])->name('application.remove-approver');
    Route::put('/application/{application}/update-approvers', [ApplicationController::class, 'updateApprovers'])->name('application.update-approvers');
    Route::post('/application/{application}', [ApplicationController::class, 'update'])->name('application.update');
    Route::post('/applications/{application}/cancel', [ApplicationController::class, 'cancelApplication'])->name('application.cancel');
    Route::post('/application/{application}/submit', [ApplicationController::class, 'submit'])->name('application.submit');
    Route::get('/application/{application}/upload-scan', [ApplicationController::class, 'uploadScan'])->name('application.upload-scan');
    Route::post('/application/{application}/upload-scan', [ApplicationController::class, 'uploadScanFiles'])->name('application.upload-scan.store');
    Route::post('/application/{application}/approve', [ApplicationController::class, 'confirmApplication'])->name('application.approve');

    Route::resource('status', StatusController::class);
    Route::post('/status/destroy-bulk', [StatusController::class, 'destroyBulk'])->name('status.destroy-bulk');

    Route::resource('currency', CurrencyController::class);
    Route::post('/currency/destroy-bulk', [CurrencyController::class, 'destroyBulk'])->name('currency.destroy-bulk');

    Route::resource('departments', DepartmentsController::class);
    Route::post('/departments/destroy-bulk', [DepartmentsController::class, 'destroyBulk'])->name('departments.destroy-bulk');

    Route::resource('projects', ProjectsController::class);
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        Route::get('/related-contracts', [ProjectsController::class, 'relatedContracts'])->name('related-contracts');
    });
    Route::post('/projects/destroy-bulk', [ProjectsController::class, 'destroyBulk'])->name('projects.destroy-bulk');

    Route::resource('contract', ContractController::class)->except(['update']);
    Route::post('/contract/{contract}/submit', [ContractController::class, 'submit'])->name('contract.submit');
    Route::post('/contract/destroy-bulk', [ContractController::class, 'destroyBulk'])->name('contract.destroy-bulk');
    Route::post('/contract/{contract}/remove-approver', [ContractController::class, 'removeApprover'])->name('contract.remove-approver');
    Route::post('/contract/{contract}/cancel', [ContractController::class, 'cancelContract'])->name('contract.cancel');
    Route::put('/contract/{contract}/update-approvers', [ContractController::class, 'updateApprovers'])->name('contract.update-approvers');
    Route::get('/contract/{contract}/upload-scan', [ContractController::class, 'uploadScan'])->name('contract.upload-scan');
    Route::post('/contract/{contract}/upload-scan', [ContractController::class, 'uploadScanFiles'])->name('contract.upload-scan.store');
    Route::post('/contract/{contract}/update', [ContractController::class, 'update'])->name('contract.update');
    Route::post('/contract/{contract}/approve', [ContractController::class, 'confirmContract'])->name('contract.approve');

    Route::resource('positions', PositionsController::class);
    Route::post('/positions/destroy-bulk', [PositionsController::class, 'destroyBulk'])->name('positions.destroy-bulk');

    Route::resource('task', TaskController::class)->except(['update']);
    Route::post('task/destroy-bulk', [TaskController::class, 'destroyBulk'])->name('task.destroy-bulk');
    Route::prefix('task/{task}')->name('task.')->group(function () {
        Route::post('/update', [TaskController::class, 'update'])->name('update');
        Route::post('/start', [TaskController::class, 'start'])->name('start');
        Route::post('/complete', [TaskController::class, 'complete'])->name('complete');
    });

    Route::resource('user', UserController::class)->except(['update']);
    Route::post('user/destroy-bulk', [UserController::class, 'destroyBulk'])->name('user.destroy-bulk');
    Route::prefix('user/{user}')->name('user.')->group(function () {
        Route::post('/update', [UserController::class, 'update'])->name('update');
    });

    Route::resource('role', RoleController::class)->except(['create', 'show', 'edit']);
    Route::post('/role/destroy-bulk', [RoleController::class, 'destroyBulk'])->name('role.destroy-bulk');

    Route::resource('permission', PermissionController::class)->except(['create', 'show', 'edit']);
    Route::post('/permission/destroy-bulk', [PermissionController::class, 'destroyBulk'])->name('permission.destroy-bulk');

    Route::resource('logs', ActivityLogController::class);

    Route::resource('contacts', ContactController::class);
    Route::post('/contacts/destroy-bulk', [ContactController::class, 'destroyBulk'])->name('contacts.destroy-bulk');
    Route::post('/contacts/cities', [ContactController::class, 'getCities'])->name('contacts.cities');
    Route::post('/contacts/subcategories', [ContactController::class, 'getSubcategories'])->name('contacts.subcategories');

    Route::resource('contact-categories', ContactCategoryController::class);
    Route::post('/contact-categories/destroy-bulk', [ContactCategoryController::class, 'destroyBulk'])->name('contact-categories.destroy-bulk');

    Route::resource('contact-subcategories', ContactSubcategoryController::class);
    Route::post('/contact-subcategories/destroy-bulk', [ContactSubcategoryController::class, 'destroyBulk'])->name('contact-subcategories.destroy-bulk');

});

require __DIR__.'/auth.php';
