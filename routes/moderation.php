<?php

use App\Http\Controllers\Moderation\DashboardController;
use App\Http\Controllers\Moderation\LogController;
use App\Http\Controllers\Moderation\ReportController;
use App\Http\Controllers\Moderation\TopicModerationController;
use App\Http\Controllers\Moderation\UserModerationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:moderator|admin'])->prefix('moderation')->name('moderation.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::patch('/topics/{topic:slug}/lock', [TopicModerationController::class, 'lock'])->name('topics.lock');
    Route::patch('/topics/{topic:slug}/unlock', [TopicModerationController::class, 'unlock'])->name('topics.unlock');
    Route::patch('/topics/{topic:slug}/pin', [TopicModerationController::class, 'pin'])->name('topics.pin');
    Route::patch('/topics/{topic:slug}/unpin', [TopicModerationController::class, 'unpin'])->name('topics.unpin');
    Route::patch('/topics/{topic:slug}/move', [TopicModerationController::class, 'move'])->name('topics.move');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}/resolve', [ReportController::class, 'resolve'])->name('reports.resolve');
    Route::patch('/reports/{report}/dismiss', [ReportController::class, 'dismiss'])->name('reports.dismiss');

    Route::get('/users', [UserModerationController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/warnings', [UserModerationController::class, 'warn'])->name('users.warn');
    Route::post('/users/{user}/suspend', [UserModerationController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [UserModerationController::class, 'unsuspend'])->name('users.unsuspend');

    Route::get('/log', LogController::class)->name('log.index');
});
