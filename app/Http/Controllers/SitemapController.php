<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Topic;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = collect([
                ['loc' => route('home'), 'priority' => '1.0'],
                ['loc' => route('categories.index'), 'priority' => '0.8'],
            ]);

            Category::query()->get()->each(function (Category $category) use ($urls) {
                $urls->push(['loc' => route('categories.show', $category), 'priority' => '0.7']);
            });

            Topic::query()->whereNull('deleted_at')->with('category')->get(['id', 'slug', 'category_id', 'updated_at'])->each(function (Topic $topic) use ($urls) {
                $urls->push([
                    'loc' => route('topics.show', [$topic->category, $topic]),
                    'lastmod' => $topic->updated_at->toAtomString(),
                    'priority' => '0.5',
                ]);
            });

            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
            foreach ($urls as $url) {
                $xml .= '  <url>'."\n";
                $xml .= '    <loc>'.e($url['loc']).'</loc>'."\n";
                if (isset($url['lastmod'])) {
                    $xml .= '    <lastmod>'.$url['lastmod'].'</lastmod>'."\n";
                }
                $xml .= '    <priority>'.$url['priority'].'</priority>'."\n";
                $xml .= '  </url>'."\n";
            }
            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
