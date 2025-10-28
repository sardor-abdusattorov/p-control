<?php

namespace App\Services\Application;

use App\Models\Application;
use App\Models\Approvals;
use App\Models\User;
use App\Repositories\ApplicationRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationService
{
    public function __construct(
        protected ApplicationRepository $repository
    ) {}

    /**
     * Create a new application with files and approvals
     */
    public function create(array $data, ?array $files = null, ?array $recipientIds = null): Application
    {
        DB::beginTransaction();

        try {
            $application = $this->repository->create([
                'title' => $data['title'],
                'project_id' => $data['project_id'],
                'currency_id' => $data['currency_id'],
                'user_id' => auth()->id(),
                'status_id' => Application::STATUS_NEW,
                'type' => $data['type'],
            ]);

            $this->logActivity('Создана заявка', $application, [
                'title' => $application->title,
                'project_id' => $application->project_id,
                'user_id' => $application->user_id,
            ]);

            if ($files) {
                $this->attachFiles($application, $files, 'documents');
            }

            if ($recipientIds) {
                $this->createApprovals($application, $recipientIds);
            }

            DB::commit();

            return $application;

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logActivity('Ошибка при создании заявки', null, [
                'error' => $th->getMessage(),
            ]);

            throw $th;
        }
    }

    /**
     * Update an application
     */
    public function update(Application $application, array $data, ?array $files = null, ?array $recipientIds = null, ?array $deletedFileIds = null): Application
    {
        DB::beginTransaction();

        try {
            $isNew = $application->status_id === Application::STATUS_NEW;

            if ($application->status_id === Application::STATUS_APPROVED) {
                throw new \Exception(__('app.label.cannot_update_approved'));
            }

            // Update approvals based on status
            if ($isNew) {
                $application->approvals()->delete();
                if ($recipientIds) {
                    $this->createApprovals($application, $recipientIds, Approvals::STATUS_NEW);
                }
            } else {
                $application->approvals()->update(['approved' => Approvals::STATUS_INVALIDATED]);
                if ($recipientIds && $data['type'] != 2) {
                    $this->createApprovals($application, $recipientIds, Approvals::STATUS_NEW);
                }
            }

            // Update application data
            $this->repository->update($application, [
                'title' => $data['title'],
                'project_id' => $data['project_id'],
                'type' => $data['type'],
                'currency_id' => $data['currency_id'],
                'status_id' => Application::STATUS_NEW,
            ]);

            // Handle file deletions
            if ($deletedFileIds) {
                $this->deleteFiles($application, $deletedFileIds);
            }

            // Handle new file uploads
            if ($files) {
                $this->attachFiles($application, $files, 'documents');
            }

            $this->logActivity('Обновлена заявка', $application, [
                'updated_fields' => ['title' => $data['title'], 'project_id' => $data['project_id'], 'type' => $data['type']],
                'application_id' => $application->id,
            ]);

            DB::commit();

            return $application->fresh();

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logActivity('Ошибка при обновлении заявки', $application, [
                'error' => $th->getMessage(),
                'application_id' => $application->id,
            ]);

            throw $th;
        }
    }

    /**
     * Delete an application
     */
    public function delete(Application $application): void
    {
        if (
            $application->type == Application::TYPE_REQUEST &&
            $application->status_id !== Application::STATUS_NEW
        ) {
            throw new \Exception(__('app.label.cannot_delete_approved_request'));
        }

        if ($this->repository->hasNonNewApprovals($application)) {
            throw new \Exception(__('app.label.cannot_delete_has_progress'));
        }

        DB::beginTransaction();

        try {
            $application->approvals()->delete();
            $application->clearMediaCollection('documents');
            $application->clearMediaCollection('scans');

            $this->logActivity('Удалена заявка и её черновики согласований', $application, [
                'application_id' => $application->id,
                'title' => $application->title,
            ]);

            $this->repository->delete($application);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logActivity('Ошибка при удалении заявки', $application, [
                'error' => $th->getMessage(),
                'application_id' => $application->id,
            ]);

            throw $th;
        }
    }

    /**
     * Bulk delete applications
     */
    public function deleteBulk(array $ids, User $user): int
    {
        if (!$user->hasRole('superadmin')) {
            throw new \Exception(__('app.label.permission_denied'));
        }

        try {
            $applications = $this->repository->findByIds($ids);

            foreach ($applications as $application) {
                $application->approvals()->delete();
                $application->clearMediaCollection('documents');
                $application->clearMediaCollection('scans');
                $application->delete();

                $this->logActivity('Удалена заявка (bulk)', $application, [
                    'application_id' => $application->id,
                    'title' => $application->title,
                ], $user);
            }

            return count($applications);

        } catch (\Throwable $th) {
            $this->logActivity('Ошибка при массовом удалении заявок', null, [
                'error' => $th->getMessage(),
                'application_ids' => $ids,
            ], $user);

            throw $th;
        }
    }

    /**
     * Upload scan files for approved application
     */
    public function uploadScanFiles(Application $application, array $files): void
    {
        if ($application->status_id !== Application::STATUS_APPROVED || $application->type == Application::TYPE_MEMO) {
            throw new \Exception(__('app.label.cannot_upload_scan'));
        }

        foreach ($files as $file) {
            $name = Str::random(24) . '.' . $file->getClientOriginalExtension();
            $application->addMedia($file)
                ->usingFileName($name)
                ->toMediaCollection('scans');
        }

        $this->logActivity('Загружены скан-копии в одобренную заявку', $application, [
            'application_id' => $application->id,
            'uploaded_files' => collect($files)->pluck('name'),
        ]);
    }

    /**
     * Attach files to application
     */
    protected function attachFiles(Application $application, array $files, string $collection = 'documents'): void
    {
        foreach ($files as $file) {
            $ext = $file instanceof UploadedFile ? $file->extension() : $file->getClientOriginalExtension();
            $name = Str::random(24) . '.' . $ext;

            $application->addMedia($file)
                ->usingFileName($name)
                ->toMediaCollection($collection);
        }
    }

    /**
     * Delete specific files from application
     */
    protected function deleteFiles(Application $application, array $fileIds): void
    {
        foreach ($fileIds as $fileId) {
            $media = $application->media()->where('id', $fileId)->first();
            if ($media) {
                $media->delete();
            }
        }
    }

    /**
     * Create approvals for application
     */
    protected function createApprovals(Application $application, array $recipientIds, int $status = null): void
    {
        $status = $status ?? Approvals::STATUS_NEW;

        foreach ($recipientIds as $recipientId) {
            $application->approvals()->create([
                'user_id' => $recipientId,
                'approved' => $status,
            ]);
        }
    }

    /**
     * Log activity
     */
    protected function logActivity(string $message, ?Application $application = null, array $properties = [], ?User $user = null): void
    {
        $activity = activity('application')
            ->causedBy($user ?? auth()->user())
            ->withProperties($properties);

        if ($application) {
            $activity->performedOn($application);
        }

        $activity->log($message);
    }
}
