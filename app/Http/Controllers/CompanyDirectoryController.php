<?php

namespace App\Http\Controllers;

use App\Models\CareerApplication;
use App\Models\CompanyProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyDirectoryController extends Controller
{
    private const FIELDS = [
        'initial_salary_yen' => ['label' => '初任給', 'unit' => '円/月', 'direction' => 'desc'],
        'average_annual_salary_yen' => ['label' => '平均年収', 'unit' => '円', 'direction' => 'desc'],
        'average_salary_30s_yen' => ['label' => '30代平均年収', 'unit' => '円', 'direction' => 'desc'],
        'average_overtime_hours' => ['label' => '平均残業時間', 'unit' => '時間/月', 'direction' => 'asc'],
        'annual_holidays_days' => ['label' => '年間休日', 'unit' => '日', 'direction' => 'desc'],
        'paid_leave_rate' => ['label' => '有給取得率', 'unit' => '%', 'direction' => 'desc'],
        'housing_allowance_yen' => ['label' => '住宅補助', 'unit' => '円/月', 'direction' => 'desc'],
    ];

    public function index(Request $request): View
    {
        $this->ensureLocalPreview();
        $category = in_array($request->query('category'), ['main', 'summer', 'autumn'], true) ? $request->query('category') : null;
        $profiles = CompanyProfile::query()
            ->where(function ($query): void {
                foreach (array_keys(self::FIELDS) as $field) {
                    $query->orWhereNotNull($field);
                }
                foreach (['benefits', 'work_locations', 'notes'] as $field) {
                    $query->orWhere(fn ($textQuery) => $textQuery->whereNotNull($field)->where($field, '<>', ''));
                }
            })
            ->whereHas('applications', fn ($query) => $category ? $query->where('category', $category) : $query)
            ->with(['applications' => function ($query) use ($category): void {
                if ($category) $query->where('category', $category);
                $query->orderBy('category')->orderBy('company');
            }])
            ->orderBy('company_name')->get();

        $rankings = collect(self::FIELDS)->map(function (array $fieldInfo, string $field) use ($profiles): array {
            $ranked = $profiles->filter(fn ($profile) => $profile->{$field} !== null)
                ->sort(function ($left, $right) use ($fieldInfo, $field): int {
                    $comparison = (float) $left->{$field} <=> (float) $right->{$field};
                    return $fieldInfo['direction'] === 'desc' ? -$comparison : $comparison;
                })->values();
            $priorValue = null;
            $priorRank = null;
            return ['key' => $field, ...$fieldInfo, 'companies' => $ranked->map(function ($profile, $index) use ($field, &$priorValue, &$priorRank): array {
                $value = (float) $profile->{$field};
                $rank = $priorRank !== null && $value === $priorValue ? $priorRank : $index + 1;
                $priorValue = $value;
                $priorRank = $rank;
                return ['profile' => $profile, 'rank' => $rank, 'value' => $profile->{$field}];
            })->all()];
        })->values()->all();

        $editableApplications = CareerApplication::query()->with('companyProfile')->orderBy('company')->get()
            ->filter(fn ($application) => (! $category || $application->category === $category)
                && $application->companyProfile && ! $this->profileHasData($application->companyProfile))
            ->unique('company_profile_id')->values();

        return view('research.index', [
            'profiles' => $profiles,
            'rankings' => $rankings,
            'editableApplications' => $editableApplications,
            'activeCategory' => $category,
            'categories' => ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン'],
        ]);
    }

    public function edit(CareerApplication $application): View
    {
        $this->ensureLocalPreview();
        $profile = $application->companyProfile;
        if (! $profile) {
            $profile = auth()->user()->companyProfiles()->firstOrCreate(['company_name' => trim($application->company)]);
            $application->companyProfile()->associate($profile)->save();
        }
        return view('research.edit', ['profile' => $profile, 'application' => $application,
            'categories' => ['main' => '本選考', 'summer' => '夏インターン', 'autumn' => '秋・冬インターン']]);
    }

    public function update(Request $request, CompanyProfile $profile): RedirectResponse
    {
        $this->ensureLocalPreview();
        $validated = $request->validate([
            'initial_salary_yen' => ['nullable', 'integer', 'between:0,100000000'],
            'average_annual_salary_yen' => ['nullable', 'integer', 'between:0,100000000'],
            'average_salary_30s_yen' => ['nullable', 'integer', 'between:0,100000000'],
            'average_overtime_hours' => ['nullable', 'numeric', 'between:0,1000'],
            'annual_holidays_days' => ['nullable', 'integer', 'between:0,366'],
            'paid_leave_rate' => ['nullable', 'numeric', 'between:0,100'],
            'housing_allowance_yen' => ['nullable', 'integer', 'between:0,100000000'],
            'benefits' => ['nullable', 'string', 'max:5000'],
            'work_locations' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'category' => ['nullable', Rule::in(['main', 'summer', 'autumn'])],
        ]);
        unset($validated['category']);
        $profile->fill($validated)->save();
        return to_route('research.index', ['category' => $request->input('category')])
            ->with('research_saved', '企業情報を保存しました。ランキングを更新しました。');
    }

    private function profileHasData(CompanyProfile $profile): bool
    {
        foreach (array_keys(self::FIELDS) as $field) if ($profile->{$field} !== null) return true;
        foreach (['benefits', 'work_locations', 'notes'] as $field) if (filled($profile->{$field})) return true;
        return false;
    }

    private function ensureLocalPreview(): void
    {
        abort_unless(app()->environment('local') || config('services.career_ai.web_enabled'), 404);
    }
}
