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
        $pendingDocuments = $applications->flatMap(fn (CareerApplication $application) => $application->documents);
        $documentsThisWeek = $applications->flatMap(fn (CareerApplication $application) => $application->documents)->filter(fn ($document): bool => $document->updated_at?->isCurrentWeek());

        return view('home', [
            'categoryCounts' => $categoryCounts,
            'resultWaiting' => $resultWaiting,
            'selectionEvents' => $selectionEvents,
            'pendingDocuments' => $pendingDocuments,
            'documentsThisWeek' => $documentsThisWeek,
            'researchCount' => $applications->filter(fn (CareerApplication $application): bool => $application->research_count > 0)->count(),
            'totalApplications' => $applications->count(),
            'preparedDocuments' => $applications->sum('documents_count'),
            'neonUsage' => $neonUsage->forUser(auth()->user()),
        ]);
    }
}
