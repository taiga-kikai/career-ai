<?php

namespace App\Http\Controllers;

use App\EsQuestionParser;
use App\Models\ApplicationDocument;
use App\Models\CareerApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApplicationDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureLocalPreview();

        $category = in_array($request->query('category'), ['main', 'summer', 'autumn'], true) ? $request->query('category') : 'main';
        $status = in_array($request->query('status'), ['all', 'filled', 'unfilled'], true) ? $request->query('status') : 'all';
        $applications = CareerApplication::query()->withCount('documents')->orderBy('company')->get();
        $byCategory = $applications->groupBy('category');
        $visibleApplications = $byCategory->get($category, collect())->filter(fn (CareerApplication $application): bool => match ($status) {
            'filled' => $application->documents_count > 0,
            'unfilled' => $application->documents_count === 0,
            default => true,
        })->values();

        return view('documents.index', [
            'applications' => $visibleApplications,
            'category' => $category,
            'status' => $status,
            'counts' => collect(['main', 'summer', 'autumn'])->mapWithKeys(fn (string $key): array => [$key => $byCategory->get($key, collect())->count()]),
        ]);
    }

    public function create(Request $request): View
    {
        $this->ensureLocalPreview();

        return view('documents.create', [
            'applications' => CareerApplication::query()->orderBy('company')->get(),
            'selectedCategory' => in_array($request->query('category'), ['main', 'summer', 'autumn'], true) ? $request->query('category') : 'main',
            'selectedApplication' => $request->query('application'),
        ]);
    }

    public function parse(Request $request, EsQuestionParser $parser): JsonResponse
    {
        $this->ensureLocalPreview();
        $validated = $request->validate(['raw_text' => ['required', 'string', 'max:50000']]);

        return response()->json($parser->parse($validated['raw_text']));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureLocalPreview();
        $validated = $this->validateDocument($request);
        $application = CareerApplication::query()->findOrFail($validated['career_application_id']);
        $application->documents()->create([
            'title' => $validated['title'],
            'raw_text' => $validated['raw_text'],
            'questions' => $this->cleanQuestions($validated['questions']),
        ]);

        return to_route('documents.application', $application)->with('document_saved', 'ES・書類を追加しました。');
    }

    public function showApplication(CareerApplication $application): View
    {
        $this->ensureLocalPreview();
        $application->load(['documents' => fn ($query) => $query->orderByDesc('created_at')]);

        return view('documents.application', ['application' => $application]);
    }

    public function edit(ApplicationDocument $document): View
    {
        $this->ensureLocalPreview();
        $document->load('application');

        return view('documents.edit', ['document' => $document]);
    }

    public function update(Request $request, ApplicationDocument $document): RedirectResponse
    {
        $this->ensureLocalPreview();
        $validated = $this->validateDocument($request, false);
        $document->update([
            'title' => $validated['title'],
            'raw_text' => $validated['raw_text'],
            'questions' => $this->cleanQuestions($validated['questions']),
        ]);

        return to_route('documents.application', $document->application)->with('document_saved', '変更を保存しました。');
    }

    public function destroy(ApplicationDocument $document): RedirectResponse
    {
        $this->ensureLocalPreview();
        $application = $document->application;
        $document->delete();

        return to_route('documents.application', $application)->with('document_saved', 'ES・書類を削除しました。');
    }

    /** @return array<string, mixed> */
    private function validateDocument(Request $request, bool $includeApplication = true): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'raw_text' => ['required', 'string', 'max:50000'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string', 'max:1500'],
            'questions.*.answer' => ['nullable', 'string', 'max:8000'],
        ];

        if ($includeApplication) {
            $rules['career_application_id'] = ['required', 'integer', 'exists:career_applications,id'];
        }

        return $request->validate($rules);
    }

    /**
     * @param  array<int, array{question: string, answer?: string|null}>  $questions
     * @return array<int, array{question: string, answer: string}>
     */
    private function cleanQuestions(array $questions): array
    {
        return collect($questions)->map(fn (array $question): array => [
            'question' => trim($question['question']),
            'answer' => trim($question['answer'] ?? ''),
        ])->values()->all();
    }

    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }
}
