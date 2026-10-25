<?php

use App\Enums\Role;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:5,1')->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', fn () => auth()->user()->role === Role::Customer ? redirect()->route('portal') : redirect()->route('dashboard'));
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/attachments/{attachment}', [TicketController::class, 'download'])->name('attachments.download');

    Route::middleware('role:customer')->prefix('portal')->group(function () {
        Route::get('/', [PortalController::class, 'index'])->name('portal');
        Route::get('/tickets', [PortalController::class, 'index'])->name('portal.tickets');
        Route::get('/tickets/create', [PortalController::class, 'create'])->name('portal.create');
        Route::post('/tickets', [PortalController::class, 'store'])->name('portal.store');
        Route::get('/tickets/{ticket}', [PortalController::class, 'show'])->name('portal.show');
        Route::post('/tickets/{ticket}/reply', [PortalController::class, 'reply'])->name('portal.reply');
        Route::post('/tickets/{ticket}/close', [PortalController::class, 'close'])->name('portal.close');
        Route::post('/tickets/{ticket}/reopen', [PortalController::class, 'reopen'])->name('portal.reopen');
        Route::post('/tickets/{ticket}/rate', [PortalController::class, 'rate'])->name('portal.rate');
    });

    Route::middleware('role:admin,agent')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets/bulk', [TicketController::class, 'bulk'])->name('tickets.bulk');
        Route::post('/tickets/{ticket}/merge', [TicketController::class, 'merge'])->name('tickets.merge');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply'])->name('tickets.reply');
        Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
        Route::get('/customers', [TicketController::class, 'customers'])->name('customers');
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [DashboardController::class, 'export'])->name('reports.export');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::post('/users', [AdminController::class, 'saveUser'])->name('users.save');
        Route::get('/catalog', [AdminController::class, 'catalog'])->name('catalog');
        Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
        Route::get('/departments', [AdminController::class, 'departments'])->name('departments');
        Route::get('/tags', [AdminController::class, 'tags'])->name('tags');
        Route::get('/sla', [AdminController::class, 'sla'])->name('sla');
        Route::get('/canned-responses', [AdminController::class, 'cannedResponses'])->name('canned');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings/company', [AdminController::class, 'saveSettings'])->name('settings.save');
        Route::post('/settings/sla', [AdminController::class, 'saveSla'])->name('sla.save');
        Route::post('/settings/canned', [AdminController::class, 'saveCanned'])->name('canned.save');
        Route::delete('/settings/canned/{canned}', [AdminController::class, 'deleteCanned'])->name('canned.delete');
        Route::post('/catalog/{type}', [AdminController::class, 'saveCatalog'])->name('catalog.save');
        Route::delete('/catalog/{type}/{id}', [AdminController::class, 'deleteCatalog'])->name('catalog.delete');
    });
});
