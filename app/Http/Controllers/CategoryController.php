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
            ->withSum('votes as score', 'value')
            ->with(['votes' => fn ($q) => $q->where('user_id', auth()->id() ?? 0)])
            ->orderByDesc('pinned_at')
            ->latest()
            ->paginate(20);

        // Tous ces sujets appartiennent à $category : on évite un N+1 sur topic-row (qui a besoin
        // de $topic->category pour son URL) en réutilisant simplement le modèle déjà en main.
        $topics->getCollection()->each->setRelation('category', $category);

        $categories = Category::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug', 'is_readonly']);

        return view('categories.show', compact('category', 'topics', 'categories'));
    }
}
