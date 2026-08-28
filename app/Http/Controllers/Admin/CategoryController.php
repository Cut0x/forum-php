<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories', ['categories' => $this->all()]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        Category::query()->create([
            'name' => $data['name'],
            'slug' => Category::uniqueSlug($data['name']),
            'description' => $data['description'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_readonly' => $request->boolean('is_readonly'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return $this->respond($request, 'Catégorie créée.');
    }

    public function update(Request $request, Category $category): RedirectResponse|Response
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = $data['name'] !== $category->name ? Category::uniqueSlug($data['name'], $category->id) : $category->slug;

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'is_readonly' => $request->boolean('is_readonly'),
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return $this->respond($request, 'Catégorie mise à jour.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse|Response
    {
        if ($category->topics()->withTrashed()->exists()) {
            return $this->respond($request, 'Impossible de supprimer une catégorie contenant des sujets.', 'error');
        }

        $category->delete();

        return $this->respond($request, 'Catégorie supprimée.');
    }

    protected function all()
    {
        return Category::query()->orderBy('sort_order')->orderBy('name')->get();
    }

    protected function respond(Request $request, string $message, string $type = 'success'): RedirectResponse|Response
    {
        if ($request->ajax()) {
            return $this->fragment(view('admin.categories._panel', ['categories' => $this->all()]), $message, $type);
        }

        return back()->with($type, $message);
    }
}
