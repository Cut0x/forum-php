<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount(['topics' => fn ($q) => $q->whereNull('deleted_at')])
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
        $topics = $category->topics()
            ->with('user')
            ->withCount('posts')
            ->orderByDesc('pinned_at')
            ->latest()
            ->paginate(20);

        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug', 'is_readonly']);

        return view('categories.show', compact('category', 'topics', 'categories'));
    }
}
