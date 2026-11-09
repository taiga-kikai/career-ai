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
            ->where('is_completed', false)
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->addDays(30))
            ->orderBy('starts_at')
            ->limit(12)
            ->get();
        $deadlineNames = ['es_deadline' => 'ES提出締切', 'aptitude_deadline' => '適性検査締切'];
        $mascotTasks = $selectionEvents->take(3)->map(function (ScheduleEvent $event) use ($deadlineNames): array {
            $start = $event->starts_at->timezone('Asia/Tokyo');
            $isDeadline = in_array($event->event_type, ['es_deadline', 'aptitude_deadline'], true);
            $companyName = $event->company_name ?: '企業';
            $taskName = $isDeadline
                ? ($deadlineNames[$event->event_type] ?? '締切')
                : ($event->title ?: '予定');

            return [
                'date' => $start->locale('ja')->isoFormat('M/D（ddd）'),
                'time' => $isDeadline
                    ? '〜'.$start->format('H:i')
                    : $start->format('H:i').($event->ends_at ? '〜'.$event->ends_at->timezone('Asia/Tokyo')->format('H:i') : ''),
                'text' => $isDeadline
                    ? $companyName.'の'.$taskName
                    : $taskName.($event->company_name ? ' · '.$event->company_name : ''),
            ];
        })->values();
        $mascotMessage = $mascotTasks->isNotEmpty()
            ? '近い締切・予定をまとめたよ。今日できる一歩から進めよう！'
            : '今日できることをひとつ。自分のペースで大丈夫！';
        $pendingDocuments = $applications->flatMap(fn (CareerApplication $application) => $application->documents);
        $documentsThisWeek = $applications->flatMap(fn (CareerApplication $application) => $application->documents)->filter(fn ($document): bool => $document->updated_at?->isCurrentWeek());

        return view('home', [
            'categoryCounts' => $categoryCounts,
            'plannedApplications' => $plannedApplications,
            'resultWaiting' => $resultWaiting,
            'selectionEvents' => $selectionEvents,
            'mascotMessage' => $mascotMessage,
            'mascotTasks' => $mascotTasks,
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
