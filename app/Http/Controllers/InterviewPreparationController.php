<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** 応募先ごとの面接準備を一覧表示・保存・削除する。 */
class InterviewPreparationController extends Controller
{
    /** 選考区分ごとの応募先、準備状況、件数を一覧画面へ渡す。 */
    public function index(Request $request): View
    {
        $this->ensureLocalPreview();
        $category = in_array($request->query('category'), ['main', 'summer', 'autumn'], true) ? $request->query('category') : 'main';
        $applications = CareerApplication::query()->where('category', $category)->withCount('interviewPreparation')->orderBy('company')->get();
        $counts = CareerApplication::query()->selectRaw('category, count(*) as total')->groupBy('category')->pluck('total', 'category');

        return view('interviews.index', compact('applications', 'category', 'counts'));
    }

    /** 対象企業の面接準備内容を読み込み、詳細・編集画面を表示する。 */
    public function show(CareerApplication $application): View
    {
        $this->ensureLocalPreview();
        $application->load('interviewPreparation');

        return view('interviews.show', compact('application'));
    }

    /** 面接用メモと空欄を除いた練習質問を検証して保存する。 */
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

    /** 対象応募先の面接準備レコードを削除する。 */
    public function destroy(CareerApplication $application): RedirectResponse
    {
        $this->ensureLocalPreview();
        $application->interviewPreparation()->delete();

        return to_route('interviews.show', $application)->with('interview_saved', '面接準備を削除しました。');
    }

    /** ローカルまたは機能有効化済み環境でのみ面接対策を表示可能にする。 */
    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }
}
