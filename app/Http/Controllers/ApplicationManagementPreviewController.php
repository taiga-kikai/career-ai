<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ApplicationManagementPreviewController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->ensureLocalPreview();

        $applications = CareerApplication::query()->orderBy('id')->get();
        $rowsByCategory = $applications->groupBy('category');
        $customStatusOptions = DB::table('application_status_options')
            ->where('user_id', auth()->id())
            ->orderBy('label')
            ->get()
            ->groupBy('category')
            ->map(fn (Collection $categoryOptions): array => $categoryOptions->groupBy('status_group')->map(fn (Collection $groupOptions): array => $groupOptions->map(fn (object $option): array => ['id' => $option->id, 'label' => $option->label])->values()->all())->all())
            ->all();

        return view('applications', [
            'mainApplications' => $this->viewRows($rowsByCategory->get('main', collect())),
            'summerApplications' => $this->viewRows($rowsByCategory->get('summer', collect())),
            'autumnApplications' => $this->viewRows($rowsByCategory->get('autumn', collect())),
            'customStatusOptions' => $customStatusOptions,
            'prefill' => [
                'open' => $request->boolean('add') && is_string($request->query('company')) && trim($request->query('company')) !== '',
                'category' => in_array($request->query('category'), ['main', 'summer', 'autumn'], true) ? $request->query('category') : 'main',
                'company' => mb_substr(trim((string) $request->query('company')), 0, 255),
                'program' => mb_substr(trim((string) $request->query('program')), 0, 255),
            ],
            'activeCategory' => in_array($request->query('category'), ['main', 'summer', 'autumn'], true) ? $request->query('category') : 'main',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureLocalPreview();

        $application = DB::transaction(function () use ($request): CareerApplication {
            return CareerApplication::query()->create($this->validatedApplication($request));
        });

        return to_route('applications.preview', ['category' => $application->category])->with('application_saved', '応募先を追加しました。');
    }

    public function update(Request $request, CareerApplication $application): RedirectResponse
    {
        $this->ensureLocalPreview();

        DB::transaction(function () use ($request, $application): void {
            $application->update($this->validatedApplication($request));
        });

        return to_route('applications.preview', ['category' => $application->category])->with('application_saved', '変更を保存しました。');
    }

    public function destroy(CareerApplication $application): RedirectResponse
    {
        $this->ensureLocalPreview();

        $category = $application->category;
        $application->delete();

        return to_route('applications.preview', ['category' => $category])->with('application_saved', '応募先を削除しました。');
    }

    public function destroyStatusOption(Request $request, int $statusOption): JsonResponse
    {
        $this->ensureLocalPreview();

        $validated = $request->validate([
            'category' => ['required', Rule::in(['main', 'summer', 'autumn'])],
            'status_group' => ['required', 'string', 'max:40'],
        ]);

        $deleted = DB::table('application_status_options')
            ->where('id', $statusOption)
            ->where('user_id', auth()->id())
            ->where('category', $validated['category'])
            ->where('status_group', $validated['status_group'])
            ->delete();

        abort_unless($deleted === 1, 404);

        return response()->json(['deleted' => true]);
    }

    /** @return array{category: string, company: string, program: string|null, status: string, detail: string|null} */
    private function validatedApplication(Request $request): array
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(['main', 'summer', 'autumn'])],
            'company' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'status_group' => ['required', 'string', 'max:40'],
            'status_detail' => ['nullable', 'string', 'max:70'],
            'custom_status_detail' => ['nullable', 'string', 'max:70'],
            'detail' => ['nullable', 'string', 'max:3000'],
        ]);

        $allowedGroups = $validated['category'] === 'main'
            ? ['応募予定', '選考中', '結果待ち', '不合格', '内々定', '辞退', 'その他']
            : ['応募予定', '選考中', '結果待ち', '不合格', '参加確定', '辞退', 'その他'];

        if (! in_array($validated['status_group'], $allowedGroups, true)) {
            throw ValidationException::withMessages(['status_group' => 'この区分で選べる選考状況を選択してください。']);
        }

        $statusDetail = trim(($validated['custom_status_detail'] ?? '') ?: ($validated['status_detail'] ?? ''));
        $needsStatusDetail = in_array($validated['status_group'], ['選考中', '結果待ち', '不合格', 'その他'], true)
            || ($validated['category'] !== 'summer' && $validated['status_group'] === '応募予定');

        if ($needsStatusDetail && $statusDetail === '') {
            throw ValidationException::withMessages(['status_detail' => '選考状況の種類を選択してください。']);
        }

        $builtInStatusDetails = match ($validated['status_group']) {
            '応募予定' => ['普通応募', '選考優遇枠', '早期選考', '推薦応募'],
            '選考中' => ['説明会', '面接', '適性検査', 'ES', 'GD', 'ES・適性検査'],
            '結果待ち' => ['ES', '面接', '適性検査', 'GD'],
            '不合格' => ['ESで', '面接で', '適性検査で', 'GDで'],
            'その他' => ['参加済み', '応募状況未更新', '選考終了'],
            default => [],
        };

        if (($validated['custom_status_detail'] ?? '') !== '' && ! in_array($statusDetail, $builtInStatusDetails, true)) {
            DB::table('application_status_options')->insertOrIgnore([
                'user_id' => auth()->id(),
                'category' => $validated['category'],
                'status_group' => $validated['status_group'],
                'label' => $statusDetail,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'category' => $validated['category'],
            'company' => $validated['company'],
            'program' => $validated['program'] ?? null,
            'status' => $validated['status_group'].($statusDetail !== '' ? '（'.$statusDetail.'）' : ''),
            'detail' => $validated['detail'] ?? null,
        ];
    }

    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }

    /**
     * @param  Collection<int, CareerApplication>  $applications
     * @return array<int, array{id: int, company: string, program: string|null, status: string, statusGroup: string, statusDetail: string|null, filter: string, detail: string|null}>
     */
    private function viewRows(Collection $applications): array
    {
        return $applications->map(function (CareerApplication $application): array {
            [$statusGroup, $statusDetail] = $this->statusParts($application);
            $status = $statusGroup.($statusDetail !== null ? '（'.$statusDetail.'）' : '');

            return [
                'id' => $application->id,
                'company' => $application->company,
                'program' => $application->program,
                'status' => $status,
                'statusGroup' => $statusGroup,
                'statusDetail' => $statusDetail,
                'filter' => $this->statusFilter($statusGroup, $application->status),
                'detail' => $application->detail,
                'compensationSummary' => $application->compensation_summary,
                'compensationResearchedAt' => $application->compensation_researched_at?->toIso8601String(),
            ];
        })->all();
    }

    /** @return array{string, string|null} */
    private function statusParts(CareerApplication $application): array
    {
        $status = $application->status;
        $acceptedLabel = $application->category === 'main' ? '内々定' : '参加確定';
        $groups = ['応募予定', '選考中', '結果待ち', '不合格', $acceptedLabel, '辞退', 'その他'];

        if (preg_match('/^(.+?)（(.+)）$/u', $status, $matches) === 1 && in_array($matches[1], $groups, true)) {
            return [$matches[1], $matches[2]];
        }

        if (in_array($status, $groups, true)) {
            return [$status, null];
        }

        if ($status === '参加決定') {
            return [$acceptedLabel, null];
        }

        return match ($status) {
            'ES不合格' => ['不合格', 'ESで'],
            'ES結果待ち' => ['結果待ち', 'ES'],
            'GD結果待ち' => ['結果待ち', 'GD'],
            'ES・テスト確認' => ['選考中', 'ES・適性検査'],
            '説明会' => ['選考中', '説明会'],
            '早期選考枠' => ['応募予定', '早期選考'],
            '推薦応募枠' => ['応募予定', '推薦応募'],
            '選考優遇枠' => ['応募予定', '選考優遇'],
            '参加決定' => ['参加確定', null],
            '結果待ち' => ['結果待ち', null],
            '選考中' => ['選考中', null],
            '参加済み' => ['その他', '参加済み'],
            '応募済み・状況未更新' => ['その他', '応募状況未更新'],
            '受検できず終了' => ['不合格', '適性検査で'],
            '参加見送り' => ['辞退', '参加見送り'],
            'キャンセル' => ['辞退', 'キャンセル'],
            '選考終了' => ['その他', '選考終了'],
            default => str_contains($status, '不合格') || str_contains($status, '落ち')
                ? ['不合格', null]
                : ['その他', $status],
        };
    }

    private function statusFilter(string $group, string $originalStatus): string
    {
        return match ($group) {
            '応募予定' => 'route',
            '選考中' => 'in-progress',
            '結果待ち' => 'waiting',
            '不合格' => 'rejected',
            '内々定', '参加確定' => 'accepted',
            '辞退' => 'withdrawn',
            'その他' => str_contains($originalStatus, '参加済み') ? 'completed' : 'other',
            default => 'other',
        };
    }
}
