<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\FooterCategory;
use App\Support\Settings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                $siteSettings = Settings::all() + config('theme.defaults');
                $footerCategories = FooterCategory::with('links')->orderBy('sort_order')->get();
                $sidebarCategories = Category::query()
                    ->orderByDesc('is_pinned')
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get(['id', 'name', 'slug', 'is_pinned']);
                $unreadNotificationsCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
            } catch (Throwable) {
                // La base de données peut être indisponible (ex: page d'erreur 500) :
                // on retombe sur des valeurs par défaut pour ne pas casser le rendu.
                $siteSettings = config('theme.defaults');
                $footerCategories = new Collection;
                $sidebarCategories = new Collection;
                $unreadNotificationsCount = 0;
            }

            $view->with(compact('siteSettings', 'footerCategories', 'sidebarCategories', 'unreadNotificationsCount'));
        });
    }
}
