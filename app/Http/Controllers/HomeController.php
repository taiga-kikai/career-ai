<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\CompanyProfile;
use App\Models\ScheduleEvent;
use App\Services\NeonUsageService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(NeonUsageService $neonUsage): View
    {
        $applications = CareerApplication::query()->withCount('documents')->get();
        $categoryCounts = $applications->countBy('category');
        $plannedApplications = $applications->filter(fn (CareerApplication $application): bool => str_starts_with($application->status, '応募予定'))->count();
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
            'plannedApplications' => $plannedApplications,
            'resultWaiting' => $resultWaiting,
            'selectionEvents' => $selectionEvents,
            'mascotMessage' => $mascotMessage,
            'pendingDocuments' => $pendingDocuments,
            'documentsThisWeek' => $documentsThisWeek,
            'totalApplications' => $applications->count(),
            'preparedDocuments' => $applications->sum('documents_count'),
            'companyProfileCount' => CompanyProfile::query()->where(function ($query): void {
                foreach (['initial_salary_yen', 'average_annual_salary_yen', 'average_salary_30s_yen', 'average_overtime_hours', 'annual_holidays_days', 'paid_leave_rate', 'housing_allowance_yen'] as $field) {
                    $query->orWhereNotNull($field);
                }
                foreach (['benefits', 'work_locations', 'notes'] as $field) {
                    $query->orWhere(fn ($textQuery) => $textQuery->whereNotNull($field)->where($field, '<>', ''));
                }
            })->count(),
            'neonUsage' => $neonUsage->forUser(auth()->user()),
        ]);
    }
}
