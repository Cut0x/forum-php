<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\ModerationAction;
use Illuminate\View\View;

class LogController extends Controller
{
    public function __invoke(): View
    {
        $actions = ModerationAction::with('moderator')->latest()->paginate(30);

        return view('moderation.log', compact('actions'));
    }
}
