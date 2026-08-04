<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\ModerationAction;
use App\Models\Report;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $pendingReports = Report::query()->where('status', Report::STATUS_PENDING)->with('reportable', 'reporter')->latest()->take(5)->get();
        $pendingCount = Report::query()->where('status', Report::STATUS_PENDING)->count();
        $recentActions = ModerationAction::with('moderator')->latest()->take(8)->get();

        return view('moderation.dashboard', compact('pendingReports', 'pendingCount', 'recentActions'));
    }
}
