<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;
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
            ->withSum('votes as score', 'value')
            ->with(['votes' => fn ($q) => $q->where('user_id', auth()->id() ?? 0)])
            ->latest()
            ->take(20)
            ->get();

        $stats = [
            'members' => User::query()->count(),
            'topics' => Topic::query()->count(),
            'posts' => Post::query()->count(),
        ];

        return view('home', compact('categories', 'latestTopics', 'stats'));
    }
}
