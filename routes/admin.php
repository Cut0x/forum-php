<?php

use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmoteController;
use App\Http\Controllers\Admin\FooterController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/theme', [ThemeController::class, 'edit'])->name('theme.edit');
    Route::patch('/theme', [ThemeController::class, 'update'])->name('theme.update');
    Route::post('/theme/preset', [ThemeController::class, 'preset'])->name('theme.preset');
    Route::post('/theme/reset', [ThemeController::class, 'reset'])->name('theme.reset');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/footer', [FooterController::class, 'index'])->name('footer.index');
    Route::post('/footer/categories', [FooterController::class, 'storeCategory'])->name('footer.categories.store');
    Route::delete('/footer/categories/{footerCategory}', [FooterController::class, 'destroyCategory'])->name('footer.categories.destroy');
    Route::post('/footer/links', [FooterController::class, 'storeLink'])->name('footer.links.store');
    Route::delete('/footer/links/{footerLink}', [FooterController::class, 'destroyLink'])->name('footer.links.destroy');

    Route::get('/badges', [BadgeController::class, 'index'])->name('badges.index');
    Route::post('/badges', [BadgeController::class, 'store'])->name('badges.store');
    Route::patch('/badges/{badge}', [BadgeController::class, 'update'])->name('badges.update');
    Route::delete('/badges/{badge}', [BadgeController::class, 'destroy'])->name('badges.destroy');

    Route::get('/emotes', [EmoteController::class, 'index'])->name('emotes.index');
    Route::post('/emotes', [EmoteController::class, 'store'])->name('emotes.store');
    Route::patch('/emotes/{emote}', [EmoteController::class, 'update'])->name('emotes.update');
    Route::delete('/emotes/{emote}', [EmoteController::class, 'destroy'])->name('emotes.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    Route::post('/users/{user}/badges/{badge}', [UserController::class, 'attachBadge'])->name('users.badges.attach');
    Route::delete('/users/{user}/badges/{badge}', [UserController::class, 'detachBadge'])->name('users.badges.detach');
});
