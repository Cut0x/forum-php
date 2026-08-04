<?php

namespace App\Http\Controllers\Moderation;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Notifications\ReportResolved;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', Report::STATUS_PENDING);
        if (! in_array($status, [Report::STATUS_PENDING, Report::STATUS_RESOLVED, Report::STATUS_DISMISSED], true)) {
            $status = Report::STATUS_PENDING;
        }

        $reports = Report::query()
            ->where('status', $status)
            ->with(['reportable', 'reporter', 'resolver'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('moderation.reports.index', compact('reports', 'status'));
    }

    public function resolve(Request $request, Report $report): RedirectResponse|Response
    {
        $this->closeReport($request, $report, Report::STATUS_RESOLVED);

        if ($request->ajax()) {
            return $this->ajaxOk('Signalement traité.');
        }

        return back()->with('success', 'Signalement traité.');
    }

    public function dismiss(Request $request, Report $report): RedirectResponse|Response
    {
        $this->closeReport($request, $report, Report::STATUS_DISMISSED);

        if ($request->ajax()) {
            return $this->ajaxOk('Signalement rejeté.');
        }

        return back()->with('success', 'Signalement rejeté.');
    }

    protected function closeReport(Request $request, Report $report, string $status): void
    {
        $report->update([
            'status' => $status,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        $report->reporter->notify(new ReportResolved($report));
    }
}
