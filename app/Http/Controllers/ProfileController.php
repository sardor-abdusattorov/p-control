<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Department;
use App\Models\Position;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $departments = Department::where('status', 1)->get(['id', 'name']);

        $positions = Position::select('positions.id', 'positions.name', 'positions_department.department_id')
            ->join('positions_department', 'positions.id', '=', 'positions_department.position_id')
            ->get();

        $user = $request->user();

        $users = User::where('id', '!=', auth()->id())
            ->where('status', 1)
            ->whereIn('department_id', [7, 8, 9])
            ->with('department')
            ->get()
            ->groupBy(fn($user) => $user->department->name ?? __('app.label.no_department'))
            ->map(function ($users, $departmentName) {
                return [
                    'label' => $departmentName,
                    'items' => $users->map(fn($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ])->values()
                ];
            })
            ->values();

        $recipients = Recipient::where('user_id', $user->id)->get();

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'departments' => $departments,
            'positions' => $positions,
            'users' => $users,
            'recipients' => $recipients,
            'breadcrumbs'   => [['label' => __('app.label.profile'), 'href' => route('profile.edit')]],
        ]);
    }

    /**
     * Update the user's profile information.
     */

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();
        $user->telegram_id = $data['telegram_id'] ?? null;

        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        if ($request->hasFile('image')) {
            if ($user->getFirstMedia('profile_image')) {
                $user->getFirstMedia('profile_image')->delete();
            }
            $file = $request->file('image');
            $ext = $file->extension();
            $name = Str::random(24) . '.' . $ext;
            $user->addMediaFromRequest('image')
                ->usingFileName($name)
                ->toMediaCollection('profile_image');
        }

        if (isset($data['recipients'])) {
            $user->recipients()->delete();

            foreach ($data['recipients'] as $recipientId) {
                Recipient::create([
                    'user_id' => $user->id,
                    'recipient_id' => $recipientId,
                ]);
            }
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profile updated!');
    }

}
