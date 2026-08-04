<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterCategory;
use App\Models\FooterLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FooterController extends Controller
{
    public function index(): View
    {
        return view('admin.footer', ['categories' => $this->all()]);
    }

    public function storeCategory(Request $request): RedirectResponse|Response
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80']]);
        FooterCategory::query()->create($data + ['sort_order' => FooterCategory::query()->max('sort_order') + 1]);

        return $this->respond($request, 'Catégorie de footer créée.');
    }

    public function destroyCategory(Request $request, FooterCategory $footerCategory): RedirectResponse|Response
    {
        $footerCategory->delete();

        return $this->respond($request, 'Catégorie de footer supprimée.');
    }

    public function storeLink(Request $request): RedirectResponse|Response
    {
        $data = $request->validate([
            'footer_category_id' => ['required', 'exists:footer_categories,id'],
            'label' => ['required', 'string', 'max:80'],
            'url' => ['required', 'string', 'max:255'],
        ]);

        FooterLink::query()->create($data + ['sort_order' => 0]);

        return $this->respond($request, 'Lien ajouté.');
    }

    public function destroyLink(Request $request, FooterLink $footerLink): RedirectResponse|Response
    {
        $footerLink->delete();

        return $this->respond($request, 'Lien supprimé.');
    }

    protected function all()
    {
        return FooterCategory::with('links')->orderBy('sort_order')->get();
    }

    protected function respond(Request $request, string $message): RedirectResponse|Response
    {
        if ($request->ajax()) {
            return $this->fragment(view('admin.footer._panel', ['categories' => $this->all()]), $message);
        }

        return back()->with('success', $message);
    }
}
