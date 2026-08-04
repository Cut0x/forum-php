<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Editor\EmoteController;
use App\Http\Controllers\Editor\ImageUploadController;
use App\Http\Controllers\Editor\MarkdownPreviewController;
use App\Http\Controllers\Editor\MentionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostVoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/search', SearchController::class)->name('search');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::post('/theme/toggle', ThemeController::class)->name('theme.toggle');
Route::get('/emotes', EmoteController::class)->name('emotes.index');

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/topics/{topic:slug}', [TopicController::class, 'show'])->name('topics.show');
Route::get('/profile/{user:username}', [ProfileController::class, 'show'])->name('profile.show');

Route::middleware('auth')->group(function () {
    Route::post('/categories/{category:slug}/topics', [TopicController::class, 'store'])
        ->middleware('not_suspended')->name('topics.store');
    Route::patch('/topics/{topic:slug}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('/topics/{topic:slug}', [TopicController::class, 'destroy'])->name('topics.destroy');

    Route::post('/topics/{topic:slug}/posts', [PostController::class, 'store'])
        ->middleware('not_suspended')->name('posts.store');
    Route::patch('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/vote', [PostVoteController::class, 'store'])
        ->middleware('not_suspended')->name('posts.vote');

    Route::post('/topics/{topic:slug}/reports', [ReportController::class, 'storeForTopic'])
        ->middleware('not_suspended')->name('topics.reports.store');
    Route::post('/posts/{post}/reports', [ReportController::class, 'storeForPost'])
        ->middleware('not_suspended')->name('posts.reports.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::patch('/settings/email', [SettingsController::class, 'updateEmail'])->name('settings.email');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::get('/settings/export', [SettingsController::class, 'export'])->name('settings.export');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])->name('settings.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');

    Route::post('/markdown/preview', MarkdownPreviewController::class)->name('markdown.preview');
    Route::get('/mentions/search', MentionController::class)->name('mentions.search');
    Route::post('/uploads/images', ImageUploadController::class)->name('uploads.images');
});

require __DIR__.'/auth.php';
require __DIR__.'/moderation.php';
require __DIR__.'/admin.php';
