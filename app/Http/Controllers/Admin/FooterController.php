<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterCategory;
use App\Models\FooterLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FooterController extends Controller
{
    public function index(): View
    {
        $categories = FooterCategory::with('links')->orderBy('sort_order')->get();

        return view('admin.footer', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);
        FooterCategory::query()->create($data + ['sort_order' => FooterCategory::query()->max('sort_order') + 1]);

        return back()->with('success', 'Catégorie de footer créée.');
    }

    public function destroyCategory(FooterCategory $footerCategory): RedirectResponse
    {
        $footerCategory->delete();

        return back()->with('success', 'Catégorie de footer supprimée.');
    }

    public function storeLink(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'footer_category_id' => ['required', 'exists:footer_categories,id'],
            'label' => ['required', 'string', 'max:80'],
            'url' => ['required', 'string', 'max:255'],
        ]);

        FooterLink::query()->create($data + ['sort_order' => 0]);

        return back()->with('success', 'Lien ajouté.');
    }

    public function destroyLink(FooterLink $footerLink): RedirectResponse
    {
        $footerLink->delete();

        return back()->with('success', 'Lien supprimé.');
    }
}
