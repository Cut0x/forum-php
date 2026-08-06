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
     * Données partagées (site, footer, sidebar, notifications) calculées une seule fois par requête,
     * même si le composeur ci-dessous se déclenche pour plusieurs vues (ex: "home" + "layouts.app").
     */
    protected ?array $sharedViewData = null;

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
        // Uniquement les vues qui utilisent réellement ces variables — jamais '*' :
        // un composeur '*' se redéclenche à chaque composant Blade rendu (icônes, badges, votes...),
        // pas une fois par page, ce qui multipliait ces requêtes par dizaines sur une page chargée.
        View::composer(['layouts.app', 'home', 'admin.settings'], function ($view) {
            $view->with($this->sharedViewData());
        });
    }

    protected function sharedViewData(): array
    {
        if ($this->sharedViewData !== null) {
            return $this->sharedViewData;
        }

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

        return $this->sharedViewData = compact('siteSettings', 'footerCategories', 'sidebarCategories', 'unreadNotificationsCount');
    }
}
