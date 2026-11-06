<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\ScheduleEvent;
use App\Services\NeonUsageService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(NeonUsageService $neonUsage): View
    {
        $applications = CareerApplication::query()->withCount(['documents', 'research'])->get();
        $categoryCounts = $applications->countBy('category');
        $resultWaiting = $applications->filter(fn (CareerApplication $application): bool => str_contains($application->status, '結果待ち'))->values();
        $selectionEvents = ScheduleEvent::query()
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->addDays(30))
            ->orderBy('starts_at')
            ->limit(12)
            ->get();
        $deadlineEvent = ScheduleEvent::query()
            ->whereIn('event_type', ['es_deadline', 'aptitude_deadline'])
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->first();
        $deadlineNames = ['es_deadline' => 'ES提出', 'aptitude_deadline' => '適性検査'];
        $mascotMessage = match (true) {
            $deadlineEvent !== null => ($deadlineEvent->company_name ?: '企業') . 'の' . ($deadlineNames[$deadlineEvent->event_type] ?? '締切') . 'は' . $deadlineEvent->starts_at->timezone('Asia/Tokyo')->locale('ja')->isoFormat('M月D日（ddd）') . 'まで。今日できる分を進めよう！',
            $selectionEvents->isNotEmpty() => '次は' . ($selectionEvents->first()->company_name ?: '予定') . 'の' . ($selectionEvents->first()->title ?: '予定') . '。少しずつ準備しよう！',
            default => '今日できることをひとつ。自分のペースで大丈夫！',
        };
        $pendingDocuments = $applications->flatMap(fn (CareerApplication $application) => $application->documents);
        $documentsThisWeek = $applications->flatMap(fn (CareerApplication $application) => $application->documents)->filter(fn ($document): bool => $document->updated_at?->isCurrentWeek());

        return view('home', [
            'categoryCounts' => $categoryCounts,
            'resultWaiting' => $resultWaiting,
            'selectionEvents' => $selectionEvents,
            'mascotMessage' => $mascotMessage,
            'pendingDocuments' => $pendingDocuments,
            'documentsThisWeek' => $documentsThisWeek,
            'researchCount' => $applications->filter(fn (CareerApplication $application): bool => $application->research_count > 0)->count(),
            'totalApplications' => $applications->count(),
            'preparedDocuments' => $applications->sum('documents_count'),
            'neonUsage' => $neonUsage->forUser(auth()->user()),
        ]);
    }
}
