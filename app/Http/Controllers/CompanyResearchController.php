<?php

namespace App\Http\Controllers;

use App\CompanyResearchAssistant;
use App\Models\CareerApplication;
use App\Models\CompanyResearch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class CompanyResearchController extends Controller
{
    public function show(CareerApplication $application): View
    {
        $this->ensureLocalPreview();
        $application->load('research');

        return view('research.show', compact('application'));
    }

    public function save(Request $request, CareerApplication $application): RedirectResponse
    {
        $this->ensureLocalPreview();
        $validated = $request->validate([
            'industry' => ['nullable', 'string', 'max:255'],
            'business_outline' => ['nullable', 'string', 'max:20000'],
            'products_services' => ['nullable', 'string', 'max:20000'],
            'strengths' => ['nullable', 'string', 'max:20000'],
            'competitors' => ['nullable', 'string', 'max:20000'],
            'culture' => ['nullable', 'string', 'max:20000'],
            'recent_topics' => ['nullable', 'string', 'max:20000'],
            'motivation' => ['nullable', 'string', 'max:20000'],
            'sources' => ['nullable', 'array'],
            'sources.*' => ['array'],
            'sources.*.label' => ['nullable', 'string', 'max:255'],
            'sources.*.url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:30000'],
        ]);
        $validated['sources'] = collect($validated['sources'] ?? [])
            ->map(fn (array $source): array => ['label' => trim($source['label'] ?? ''), 'url' => trim($source['url'] ?? '')])
            ->filter(fn (array $source): bool => $source['label'] !== '' || $source['url'] !== '')
            ->values()
            ->all();
        $application->research()->updateOrCreate([], $validated);

        return to_route('research.show', $application)->with('research_saved', '企業研究を保存しました。');
    }

    public function compensationSummary(CareerApplication $application, CompanyResearchAssistant $assistant): JsonResponse
    {
        $this->ensureLocalPreview();
        try {
            $result = $assistant->summarizeCompensation($application->company);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => ($exception instanceof \RuntimeException) ? $exception->getMessage() : '待遇情報をまとめられませんでした。通信状態を確認して、もう一度お試しください。'], 422);
        }

        $application->forceFill([
            'compensation_summary' => $result,
            'compensation_researched_at' => now(),
        ])->save();

        return response()->json([
            'summary' => $result['summary'],
            'sources' => $result['sources'],
            'researched_at' => $application->compensation_researched_at?->toIso8601String(),
        ]);
    }

    public function destroy(CompanyResearch $research): RedirectResponse
    {
        $this->ensureLocalPreview();
        $application = $research->application;
        $research->delete();

        return to_route('research.show', $application)->with('research_saved', '企業研究を削除しました。');
    }

    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }
}
