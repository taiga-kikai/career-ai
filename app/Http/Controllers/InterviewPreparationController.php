<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InterviewPreparationController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureLocalPreview();
        $category = in_array($request->query('category'), ['main', 'summer', 'autumn'], true) ? $request->query('category') : 'main';
        $applications = CareerApplication::query()->where('category', $category)->withCount('interviewPreparation')->orderBy('company')->get();
        $counts = CareerApplication::query()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category');

        return view('interviews.index', compact('applications', 'category', 'counts'));
    }

    public function show(CareerApplication $application): View
    {
        $this->ensureLocalPreview();
        $application->load('interviewPreparation');

        return view('interviews.show', compact('application'));
    }

    public function save(Request $request, CareerApplication $application): RedirectResponse
    {
        $this->ensureLocalPreview();
        $validated = $request->validate([
            'self_introduction' => ['nullable', 'string', 'max:10000'],
            'motivation' => ['nullable', 'string', 'max:10000'],
            'student_experience' => ['nullable', 'string', 'max:10000'],
            'strengths' => ['nullable', 'string', 'max:10000'],
            'weaknesses' => ['nullable', 'string', 'max:10000'],
            'questions_to_ask' => ['nullable', 'string', 'max:10000'],
            'practice_questions' => ['nullable', 'array'],
            'practice_questions.*' => ['array'],
            'practice_questions.*.question' => ['nullable', 'string', 'max:2000'],
            'practice_questions.*.answer' => ['nullable', 'string', 'max:10000'],
            'notes' => ['nullable', 'string', 'max:30000'],
        ]);

        $validated['practice_questions'] = collect($validated['practice_questions'] ?? [])
            ->map(fn (array $item): array => ['question' => trim($item['question'] ?? ''), 'answer' => trim($item['answer'] ?? '')])
            ->filter(fn (array $item): bool => $item['question'] !== '' || $item['answer'] !== '')
            ->values()
            ->all();

        $application->interviewPreparation()->updateOrCreate([], $validated);

        return to_route('interviews.show', $application)->with('interview_saved', '面接準備を保存しました。');
    }

    public function destroy(CareerApplication $application): RedirectResponse
    {
        $this->ensureLocalPreview();
        $application->interviewPreparation()->delete();

        return to_route('interviews.show', $application)->with('interview_saved', '面接準備を削除しました。');
    }

    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }
}
