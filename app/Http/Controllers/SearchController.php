<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $results = collect();

        if ($query !== '') {
            $needle = '%'.$query.'%';
            $results = Topic::query()
                ->with('category')
                ->whereNull('deleted_at')
                ->where(function ($q) use ($needle) {
                    $q->where('title', 'like', $needle)
                        ->orWhereHas('posts', fn ($p) => $p->where('content', 'like', $needle));
                })
                ->latest()
                ->limit(50)
                ->get();
        }

        return view('search.index', ['query' => $query, 'results' => $results]);
    }
}
