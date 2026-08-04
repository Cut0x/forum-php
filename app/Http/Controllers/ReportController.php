<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Post;
use App\Models\Report;
use App\Models\Topic;
use App\Models\User;
use App\Notifications\ContentReported;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function storeForTopic(StoreReportRequest $request, Topic $topic): RedirectResponse|Response
    {
        return $this->createReport($request, $topic);
    }

    public function storeForPost(StoreReportRequest $request, Post $post): RedirectResponse|Response
    {
        return $this->createReport($request, $post);
    }

    protected function createReport(StoreReportRequest $request, Topic|Post $reportable): RedirectResponse|Response
    {
        $report = $reportable->reports()->create([
            'reporter_id' => $request->user()->id,
            'reason' => $request->validated('reason'),
            'note' => $request->validated('note'),
        ]);

        $staff = User::query()->whereIn('role', [User::ROLE_MODERATOR, User::ROLE_ADMIN])->get();
        foreach ($staff as $moderator) {
            $moderator->notify(new ContentReported($report));
        }

        if ($request->ajax()) {
            return $this->ajaxOk('Signalement envoyé, merci.');
        }

        return back()->with('success', 'Signalement envoyé, merci.');
    }
}
