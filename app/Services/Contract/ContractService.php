<?php

namespace App\Services\Contract;

use App\Models\Approvals;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\ContractRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContractService
{
    public function __construct(
        protected ContractRepository $repository
    ) {}

    /**
     * Create a new contract with files and approvals
     */
    public function create(array $data, ?array $files = null, ?array $recipientIds = null): Contract
    {
        DB::beginTransaction();

        try {
            $contract = $this->repository->create([
                'contract_number' => $data['contract_number'],
                'title' => $data['title'],
                'project_id' => $data['project_id'],
                'contact_id' => $data['contact_id'] ?? null,
                'application_id' => $data['application_id'] ?? null,
                'currency_id' => $data['currency_id'],
                'user_id' => auth()->id(),
                'budget_sum' => $data['budget_sum'],
                'status' => Contract::STATUS_NEW,
                'deadline' => Carbon::parse($data['deadline'])
                    ->timezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s'),
            ]);

            $this->logActivity('Создан контракт', $contract, [
                'contract_id' => $contract->id,
                'title' => $contract->title,
                'contract_number' => $contract->contract_number,
                'project_id' => $contract->project_id,
                'budget_sum' => $contract->budget_sum,
                'status' => $contract->status,
            ]);

            if ($files) {
                $this->attachFiles($contract, $files, 'files');
            }

            if ($recipientIds) {
                $this->createApprovals($contract, $recipientIds);
            }

            DB::commit();

            return $contract;

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logActivity('Ошибка при создании контракта', null, [
                'error' => $th->getMessage(),
                'contract_number' => $data['contract_number'] ?? null,
                'title' => $data['title'] ?? null,
            ]);

            throw $th;
        }
    }

    /**
     * Update a contract
     */
    public function update(Contract $contract, array $data, ?array $files = null, ?array $recipientIds = null, ?array $deletedFileIds = null): Contract
    {
        DB::beginTransaction();

        try {
            $isNew = $contract->status === Contract::STATUS_NEW;

            if ($contract->status === Contract::STATUS_APPROVED) {
                throw new \Exception(__('app.label.cannot_update_approved'));
            }

            $originalData = $contract->getOriginal();

            // Update approvals based on status
            if ($isNew) {
                $contract->approvals()->delete();

                if ($recipientIds) {
                    $this->createApprovals($contract, $recipientIds, Approvals::STATUS_NEW);
                }
            } else {
                $contract->approvals()->update(['approved' => Approvals::STATUS_INVALIDATED]);

                if ($recipientIds && ($data['type'] ?? null) != 2) {
                    $this->createApprovals($contract, $recipientIds, Approvals::STATUS_NEW);
                }
            }

            // Update contract data
            $this->repository->update($contract, [
                'contract_number' => $data['contract_number'],
                'title' => $data['title'],
                'project_id' => $data['project_id'],
                'contact_id' => $data['contact_id'] ?? null,
                'application_id' => $data['application_id'] ?? null,
                'currency_id' => $data['currency_id'],
                'budget_sum' => $data['budget_sum'],
                'status' => Contract::STATUS_NEW,
                'deadline' => Carbon::parse($data['deadline'])
                    ->timezone(config('app.timezone'))
                    ->format('Y-m-d H:i:s'),
            ]);

            // Handle file deletions
            if ($deletedFileIds) {
                $this->deleteFiles($contract, $deletedFileIds);
            }

            // Handle new file uploads
            if ($files) {
                $this->attachFiles($contract, $files, 'files');
            }

            $this->logActivity('Контракт обновлен', $contract, [
                'before' => $originalData,
                'after' => $contract->getChanges(),
            ]);

            DB::commit();

            return $contract->fresh();

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logActivity('Ошибка при обновлении контракта', $contract, [
                'error' => $th->getMessage(),
                'contract_id' => $contract->id,
            ]);

            throw $th;
        }
    }

    /**
     * Delete a contract
     */
    public function delete(Contract $contract): void
    {
        DB::beginTransaction();

        try {
            $contractTitle = $contract->title;
            $contractId = $contract->id;

            $contract->clearMediaCollection('files');
            $contract->clearMediaCollection('scans');

            $this->logActivity('Контракт удален', $contract, [
                'contract_id' => $contractId,
                'title' => $contractTitle,
            ]);

            $this->repository->delete($contract);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logActivity('Ошибка при удалении контракта', $contract, [
                'error' => $th->getMessage(),
                'contract_id' => $contract->id,
            ]);

            throw $th;
        }
    }

    /**
     * Bulk delete contracts
     */
    public function deleteBulk(array $ids, User $user): int
    {
        if (!$user->hasRole('superadmin')) {
            throw new \Exception(__('app.label.permission_denied'));
        }

        DB::beginTransaction();

        try {
            $contracts = $this->repository->findByIds($ids);
            $deletedContracts = [];

            foreach ($contracts as $contract) {
                $deletedContracts[] = [
                    'contract_id' => $contract->id,
                    'title' => $contract->title,
                ];

                $contract->clearMediaCollection('files');
                $contract->clearMediaCollection('scans');
                $contract->delete();
            }

            $this->logActivity('Массовое удаление контрактов', null, [
                'deleted_contracts' => $deletedContracts,
            ], $user);

            DB::commit();

            return count($contracts);

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logActivity('Ошибка при массовом удалении контрактов', null, [
                'error' => $th->getMessage(),
                'contract_ids' => $ids,
            ], $user);

            throw $th;
        }
    }

    /**
     * Upload scan files for approved contract
     */
    public function uploadScanFiles(Contract $contract, array $files): void
    {
        if ($contract->status !== Contract::STATUS_APPROVED) {
            throw new \Exception(__('app.label.cannot_upload_scan'));
        }

        foreach ($files as $file) {
            $name = Str::random(24) . '.' . $file->getClientOriginalExtension();
            $contract->addMedia($file)
                ->usingFileName($name)
                ->toMediaCollection('scans');
        }

        $this->logActivity('Загружены скан-копии в одобренную заявку', $contract, [
            'application_id' => $contract->id,
            'uploaded_files' => collect($files)->pluck('name'),
        ]);
    }

    /**
     * Attach files to contract
     */
    protected function attachFiles(Contract $contract, array $files, string $collection = 'files'): void
    {
        foreach ($files as $file) {
            $ext = $file instanceof UploadedFile ? $file->extension() : $file->getClientOriginalExtension();
            $name = Str::random(24) . '.' . $ext;

            $contract->addMedia($file)
                ->usingFileName($name)
                ->toMediaCollection($collection);
        }
    }

    /**
     * Delete specific files from contract
     */
    protected function deleteFiles(Contract $contract, array $fileIds): void
    {
        foreach ($fileIds as $fileId) {
            $media = $contract->media()->where('id', $fileId)->first();
            if ($media) {
                $media->delete();
            }
        }
    }

    /**
     * Create approvals for contract
     */
    protected function createApprovals(Contract $contract, array $recipientIds, int $status = null): void
    {
        $status = $status ?? Approvals::STATUS_NEW;

        // Load users with their departments to determine approval order
        $users = User::whereIn('id', $recipientIds)->get()->keyBy('id');

        foreach ($recipientIds as $recipientId) {
            $user = $users->get($recipientId);
            $approvalOrder = $user ? Approvals::getApprovalOrder($user->department_id) : 1;

            $contract->approvals()->create([
                'user_id' => $recipientId,
                'approval_order' => $approvalOrder,
                'approved' => $status,
            ]);
        }
    }

    /**
     * Log activity
     */
    protected function logActivity(string $message, ?Contract $contract = null, array $properties = [], ?User $user = null): void
    {
        $activity = activity('contract')
            ->causedBy($user ?? auth()->user())
            ->withProperties($properties);

        if ($contract) {
            $activity->performedOn($contract);
        }

        $activity->log($message);
    }
}
