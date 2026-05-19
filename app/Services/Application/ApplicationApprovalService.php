<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Approvals;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApplicationApprovalService
{
    public function submit(Application $application, User $user): void
    {
        if (!$user->can('submit application')) {
            throw new \Exception(__('app.label.permission_denied'));
        }

        if ($application->status_id !== Application::STATUS_NEW) {
            throw new \Exception(__('app.label.cannot_submit_non_draft'));
        }

        DB::beginTransaction();

        try {
            $application->update(['status_id' => Application::STATUS_IN_PROGRESS]);

            $application->approvals()
                ->where('approved', Approvals::STATUS_NEW)
                ->update([
                    'approved' => Approvals::STATUS_PENDING,
                    'approved_at' => null,
                ]);

            $this->logActivity('Заявка отправлена на согласование', $application, [], $user);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function approve(Application $application, User $user, ?string $comment = null): void
    {
        $approval = $this->findUserApproval($application, $user);

        if (!$approval) {
            throw new \Exception(__('app.label.not_recipient'));
        }

        if (in_array($approval->approved, [Approvals::STATUS_APPROVED, Approvals::STATUS_REJECTED])) {
            throw new \Exception(__('app.label.already_approved'));
        }

        $approval->update([
            'approved' => Approvals::STATUS_APPROVED,
            'approved_at' => now(),
            'reason' => $comment,
        ]);

        $this->checkAndUpdateApplicationStatus($application);

        $this->logActivity('Пользователь подтвердил заявку', $application, [
            'application_id' => $application->id,
            'title' => $application->title,
            'approved_by' => $user->id,
            'approved_at' => now()->format('d.m.Y H:i'),
        ], $user);
    }

    public function reject(Application $application, User $user, ?string $reason = null): void
    {
        $approval = $this->findUserApproval($application, $user);

        if (!$approval) {
            throw new \Exception(__('app.label.not_recipient'));
        }

        if ($approval->approved === Approvals::STATUS_REJECTED && $approval->reason) {
            throw new \Exception(__('app.label.already_rejected'));
        }

        $approval->update([
            'approved' => Approvals::STATUS_REJECTED,
            'reason' => $reason,
            'approved_at' => now(),
        ]);

        $this->logActivity('Пользователь отклонил заявку', $application, [
            'application_id' => $application->id,
            'reason' => $reason,
            'rejected_by' => $user->id,
            'rejected_at' => now()->format('d.m.Y H:i'),
        ], $user);

        if ($application->status_id === Application::STATUS_IN_PROGRESS) {
            $application->update(['status_id' => Application::STATUS_REJECTED]);

            $this->logActivity('Заявка отклонена после отказа согласующего', $application, [
                'application_id' => $application->id,
                'previous_status' => Application::STATUS_IN_PROGRESS,
                'new_status' => Application::STATUS_REJECTED,
            ], $user);
        }

        Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', '!=', $user->id)
            ->where('approved', Approvals::STATUS_PENDING)
            ->update([
                'approved' => Approvals::STATUS_REJECTED,
                'reason' => 'Автоматически отклонено после отказа одного из согласующих',
                'approved_at' => now(),
            ]);
    }

    public function updateApprovers(Application $application, array $newUserIds): void
    {
        $newUserIds = collect($newUserIds);

        $existingApprovals = Approvals::valid()
            ->where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->get()
            ->keyBy('user_id');

        $usersToAdd = $newUserIds->diff($existingApprovals->keys());
        $usersToRemove = $existingApprovals->keys()->diff($newUserIds);

        foreach ($usersToAdd as $userId) {
            Approvals::create([
                'approvable_type' => Application::class,
                'approvable_id'   => $application->id,
                'user_id'         => $userId,
                'approved'        => $application->status_id === Application::STATUS_NEW
                    ? Approvals::STATUS_NEW
                    : Approvals::STATUS_PENDING,
            ]);
        }

        $deletableStatuses = match ($application->status_id) {
            Application::STATUS_NEW => [Approvals::STATUS_NEW],
            Application::STATUS_IN_PROGRESS => [Approvals::STATUS_PENDING],
            default => [],
        };

        if (!empty($deletableStatuses)) {
            Approvals::whereIn('user_id', $usersToRemove)
                ->where('approvable_type', Application::class)
                ->where('approvable_id', $application->id)
                ->whereIn('approved', $deletableStatuses)
                ->delete();
        }
    }

    public function removeApprover(Application $application, int $userId): void
    {
        $approval = Approvals::valid()
            ->where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $userId)
            ->first();

        if (!$approval) {
            throw new \Exception(__('app.label.not_found', ['name' => __('app.label.approver')]));
        }

        if ($application->status_id !== Application::STATUS_NEW) {
            if ($approval->approved === Approvals::STATUS_APPROVED) {
                $user = User::find($userId);
                throw new \Exception(__('app.label.cannot_delete_approved', [
                    'name' => $user?->name ?? __('app.label.unknown_user')
                ]));
            }
        }

        $approval->delete();
    }

    public function canApprove(Application $application, User $user): bool
    {
        return Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $user->id)
            ->where('approved', Approvals::STATUS_PENDING)
            ->exists();
    }

    protected function checkAndUpdateApplicationStatus(Application $application): void
    {
        $approvals = $application->approvals()
            ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
            ->get();

        if ($approvals->where('approved', Approvals::STATUS_REJECTED)->isNotEmpty()) {
            $application->update(['status_id' => Application::STATUS_REJECTED]);
            return;
        }

        if ($approvals->every(fn($a) => $a->approved === Approvals::STATUS_APPROVED)) {
            $application->update(['status_id' => Application::STATUS_APPROVED]);
            return;
        }
    }

    protected function findUserApproval(Application $application, User $user): ?Approvals
    {
        return Approvals::where('approvable_type', Application::class)
            ->where('approvable_id', $application->id)
            ->where('user_id', $user->id)
            ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
            ->first();
    }

    protected function logActivity(string $message, Application $application, array $properties = [], ?User $user = null): void
    {
        activity('application')
            ->causedBy($user ?? auth()->user())
            ->performedOn($application)
            ->withProperties($properties)
            ->log($message);
    }
}
