<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Topic;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->withCount(['topics' => fn ($q) => $q->whereNull('deleted_at')])
            ->orderByDesc('is_pinned')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $latestTopics = Topic::query()
            ->with(['category', 'user'])
            ->withCount('posts')
            ->latest()
            ->take(6)
            ->get();

        return view('home', compact('categories', 'latestTopics'));
    }
}
