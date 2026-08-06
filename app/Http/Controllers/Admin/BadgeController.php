<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeIconUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(): View
    {
        return view('admin.badges', ['badges' => $this->all()]);
    }

    public function store(Request $request, BadgeIconUploader $uploader): RedirectResponse|Response
    {
        $data = $request->validate($this->rules($request));

        unset($data['icon_file']);
        if ($request->hasFile('icon_file')) {
            $data['icon'] = $uploader->store($request->file('icon_file'));
        }

        Badge::query()->create($data);

        return $this->respond($request, 'Badge créé.');
    }

    public function update(Request $request, Badge $badge, BadgeIconUploader $uploader): RedirectResponse|Response
    {
        $data = $request->validate($this->rules($request, $badge->id));

        unset($data['icon_file']);
        if ($request->hasFile('icon_file')) {
            $data['icon'] = $uploader->store($request->file('icon_file'));
        }

        $badge->update($data);

        return $this->respond($request, 'Badge mis à jour.');
    }

    public function destroy(Request $request, Badge $badge): RedirectResponse|Response
    {
        $badge->delete();

        return $this->respond($request, 'Badge supprimé.');
    }

    protected function rules(Request $request, ?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', Rule::unique('badges', 'code')->ignore($ignoreId)],
            'icon' => ['required_without:icon_file', 'nullable', 'string', 'max:255'],
            'icon_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:2048'],
            'color' => ['required', 'string', 'max:20'],
            'rule_type' => ['required', Rule::in(array_keys(Badge::ruleTypes()))],
            'rule_value' => [
                'nullable', 'string', 'max:20',
                function ($attribute, $value, $fail) use ($request) {
                    $type = $request->input('rule_type');
                    if (in_array($type, Badge::numericRuleTypes(), true) && (! is_numeric($value) || (int) $value < 0)) {
                        $fail('La valeur doit être un nombre entier positif pour ce type de règle.');
                    }
                    if ($type === Badge::RULE_ROLE && ! in_array($value, [User::ROLE_MEMBER, User::ROLE_MODERATOR, User::ROLE_ADMIN], true)) {
                        $fail('Le rôle doit être member, moderator ou admin.');
                    }
                },
            ],
        ];
    }

    protected function all()
    {
        return Badge::query()->orderBy('name')->get();
    }

    protected function respond(Request $request, string $message): RedirectResponse|Response
    {
        if ($request->ajax()) {
            return $this->fragment(view('admin.badges._panel', ['badges' => $this->all()]), $message);
        }

        return back()->with('success', $message);
    }
}
