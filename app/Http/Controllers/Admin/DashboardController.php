<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use App\Models\Report;
use App\Models\Topic;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users' => User::query()->count(),
            'topics' => Topic::query()->count(),
            'posts' => Post::query()->count(),
            'categories' => Category::query()->count(),
            'pending_reports' => Report::query()->where('status', Report::STATUS_PENDING)->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
