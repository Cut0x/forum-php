<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
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

        return back()->with('success', 'Catégorie créée.');
    }

    public function update(Request $request, Category $category): RedirectResponse
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

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }
}
