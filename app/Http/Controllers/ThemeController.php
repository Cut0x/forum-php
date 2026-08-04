<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $current = $request->session()->get('theme', 'light');
        $next = $current === 'dark' ? 'light' : 'dark';

        $request->session()->put('theme', $next);

        return back()->withCookie(cookie('theme', $next, 60 * 24 * 365));
    }
}
