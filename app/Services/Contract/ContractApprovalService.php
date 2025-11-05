<?php

namespace App\Services\Contract;

use App\Models\Approvals;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContractApprovalService
{
    /**
     * Submit contract for approval
     */
    public function submit(Contract $contract, User $user): void
    {
        if (!$user->can('submit contract')) {
            throw new \Exception(__('app.label.permission_denied'));
        }

        if ($contract->status !== Contract::STATUS_NEW) {
            throw new \Exception(__('app.label.cannot_submit_non_draft'));
        }

        DB::beginTransaction();

        try {
            $contract->update(['status' => Contract::STATUS_IN_PROGRESS]);

            $contract->approvals()
                ->where('approved', Approvals::STATUS_NEW)
                ->update([
                    'approved' => Approvals::STATUS_PENDING,
                    'approved_at' => null,
                ]);

            $this->logActivity('Заявка отправлена на согласование', $contract, [], $user);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Approve contract by current user
     */
    public function approve(Contract $contract, User $user, ?string $comment = null): void
    {
        $approval = $this->findUserApproval($contract, $user);

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

        $this->checkAndUpdateContractStatus($contract);

        $this->logActivity('Пользователь подтвердил контракт', $contract, [
            'contract_id' => $contract->id,
            'title' => $contract->title,
            'approved_by' => $user->id,
            'approved_at' => now()->format('d.m.Y H:i'),
        ], $user);
    }

    /**
     * Reject contract by current user
     */
    public function reject(Contract $contract, User $user, ?string $reason = null): void
    {
        $approval = $this->findUserApproval($contract, $user);

        if (!$approval) {
            throw new \Exception(__('app.label.not_recipient'));
        }

        if ($approval->approved === Approvals::STATUS_REJECTED && $approval->reason) {
            throw new \Exception(__('app.label.already_rejected'));
        }

        // Update current approver
        $approval->update([
            'approved' => Approvals::STATUS_REJECTED,
            'reason' => $reason,
            'approved_at' => now(),
        ]);

        $this->logActivity('Пользователь отклонил контракт', $contract, [
            'contract_id' => $contract->id,
            'reason' => $reason,
            'rejected_by' => $user->id,
            'rejected_at' => now()->format('d.m.Y H:i'),
        ], $user);

        // Update contract status to rejected
        if ($contract->status === Contract::STATUS_IN_PROGRESS) {
            $contract->update(['status' => Contract::STATUS_REJECTED]);

            $this->logActivity('Контракт отклонён после отказа согласующего', $contract, [
                'contract_id' => $contract->id,
                'previous_status' => $contract->status,
                'new_status' => Contract::STATUS_REJECTED,
            ], $user);
        }

        // Auto-reject all other pending approvals
        Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('user_id', '!=', $user->id)
            ->where('approved', Approvals::STATUS_PENDING)
            ->update([
                'approved' => Approvals::STATUS_REJECTED,
                'reason' => 'Автоматически отклонено после отказа одного из согласующих',
                'approved_at' => now(),
            ]);
    }

    /**
     * Update approvers list for contract
     */
    public function updateApprovers(Contract $contract, array $newUserIds): void
    {
        $newUserIds = collect($newUserIds);

        $existingApprovals = Approvals::valid()
            ->where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->get()
            ->keyBy('user_id');

        $usersToAdd = $newUserIds->diff($existingApprovals->keys());
        $usersToRemove = $existingApprovals->keys()->diff($newUserIds);

        // Add new approvers
        foreach ($usersToAdd as $userId) {
            Approvals::create([
                'approvable_type' => Contract::class,
                'approvable_id'   => $contract->id,
                'user_id'         => $userId,
                'approved'        => $contract->status === Contract::STATUS_NEW
                    ? Approvals::STATUS_NEW
                    : Approvals::STATUS_PENDING,
            ]);
        }

        // Remove approvers based on contract status
        $deletableStatuses = match ($contract->status) {
            Contract::STATUS_NEW => [Approvals::STATUS_NEW],
            Contract::STATUS_IN_PROGRESS => [Approvals::STATUS_PENDING],
            default => [],
        };

        if (!empty($deletableStatuses)) {
            Approvals::whereIn('user_id', $usersToRemove)
                ->where('approvable_type', Contract::class)
                ->where('approvable_id', $contract->id)
                ->whereIn('approved', $deletableStatuses)
                ->delete();
        }
    }

    /**
     * Remove a specific approver from contract
     */
    public function removeApprover(Contract $contract, int $userId): void
    {
        $approval = Approvals::valid()
            ->where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('user_id', $userId)
            ->first();

        if (!$approval) {
            throw new \Exception(__('app.label.not_found', ['name' => __('app.label.approver')]));
        }

        if ($contract->status !== Contract::STATUS_NEW) {
            if ($approval->approved === Approvals::STATUS_APPROVED) {
                $user = User::find($userId);
                throw new \Exception(__('app.label.cannot_delete_approved', [
                    'name' => $user?->name ?? __('app.label.unknown_user')
                ]));
            }
        }

        $approval->delete();
    }

    /**
     * Check if user can approve the contract
     */
    public function canApprove(Contract $contract, User $user): bool
    {
        return Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('user_id', $user->id)
            ->where('approved', Approvals::STATUS_PENDING)
            ->exists();
    }

    /**
     * Check and update contract status based on approvals
     */
    protected function checkAndUpdateContractStatus(Contract $contract): void
    {
        $approvals = $contract->approvals()
            ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
            ->get();

        // If any approval is rejected, set status to rejected
        if ($approvals->where('approved', Approvals::STATUS_REJECTED)->isNotEmpty()) {
            $contract->update(['status' => Contract::STATUS_REJECTED]);
            return;
        }

        // If all approvals are approved, set status to approved
        if ($approvals->every(fn($a) => $a->approved === Approvals::STATUS_APPROVED)) {
            $contract->update(['status' => Contract::STATUS_APPROVED]);
            return;
        }
    }

    /**
     * Find user's approval for the contract
     */
    protected function findUserApproval(Contract $contract, User $user): ?Approvals
    {
        return Approvals::where('approvable_type', Contract::class)
            ->where('approvable_id', $contract->id)
            ->where('user_id', $user->id)
            ->where('approved', '!=', Approvals::STATUS_INVALIDATED)
            ->first();
    }

    /**
     * Log activity
     */
    protected function logActivity(string $message, Contract $contract, array $properties = [], ?User $user = null): void
    {
        activity('contract')
            ->causedBy($user ?? auth()->user())
            ->performedOn($contract)
            ->withProperties($properties)
            ->log($message);
    }
}
