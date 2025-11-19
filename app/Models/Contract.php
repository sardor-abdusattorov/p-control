<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contract extends Model implements HasMedia
{
    use InteractsWithMedia;
    use HasFactory;

    const STATUS_NEW = 1;
    const STATUS_IN_PROGRESS = 2;
    const STATUS_APPROVED = 3;
    const STATUS_REJECTED = -1;
    const STATUS_INVALIDATED = -2;

    const TYPE_EXPENSE = 1; // Расход
    const TYPE_INCOME = 2;  // Приход


    protected $table = 'contracts';

    protected $fillable = [
        'contract_number',
        'project_id',
        'application_id',
        'user_id',
        'status',
        'transaction_type',
        'currency_id',
        'title',
        'budget_sum',
        'deadline',
        'contact_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function approvals()
    {
        return $this->morphMany(Approvals::class, 'approvable');
    }


    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_NEW, 'label' => __('app.status.new')],
            ['id' => self::STATUS_IN_PROGRESS, 'label' => __('app.status.in_progress')],
            ['id' => self::STATUS_APPROVED, 'label' => __('app.status.approved')],
            ['id' => self::STATUS_REJECTED, 'label' => __('app.status.rejected')],
            ['id' => self::STATUS_INVALIDATED, 'label' => __('app.status.invalidated')],
        ];
    }

    public static function getTransactionTypes(): array
    {
        return [
            ['id' => self::TYPE_EXPENSE, 'label' => __('app.transaction_type.expense')],
            ['id' => self::TYPE_INCOME, 'label' => __('app.transaction_type.income')],
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    public function getCreatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['created_at']));
    }
    public function getUpdatedAtAttribute() {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }
    public function getFormattedApprovals()
    {
        return $this->approvals()
            ->with('user.department')
            ->orderBy('approval_order')
            ->get()
            ->map(fn($approval) => [
                'user_id' => $approval->user_id,
                'user_name' => optional($approval->user)->name,
                'user_avatar' => optional($approval->user)->profile_image ?? '/images/no_image.png',
                'approved' => $approval->approved,
                'approved_at' => optional($approval->approved_at)?->format('d.m.Y H:i'),
                'updated_at' => optional($approval->updated_at)?->format('d.m.Y H:i'),
                'reason' => $approval->reason,
                'approval_order' => $approval->approval_order,
                'department_name' => optional(optional($approval->user)->department)->name ?? __('app.label.unknown_department'),
            ]);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function application()
    {
        return $this->belongsTo(\App\Models\Application::class, 'application_id');
    }


}
