<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;

    const STATUS_ACTIVE = 1;
    const STATUS_DISABLED = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'department_id', 'telegram_id', 'position_id', 'status',
    ];

    public static function getStatuses(): array
    {
        return [
            ['id' => self::STATUS_DISABLED, 'label' => __('app.status.disable')],
            ['id' => self::STATUS_ACTIVE, 'label' => __('app.status.active')],
        ];
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['profile_image'];

    /**
     * The attributes that should be appended to the model's array form.
     *
     * @var array<int, string>
     */

    public function getCreatedAtAttribute()
    {
        return date('d-m-Y H:i',    strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute()
    {
        return date('d-m-Y H:i', strtotime($this->attributes['updated_at']));
    }

    public function getEmailVerifiedAtAttribute()
    {
        return $this->attributes['email_verified_at'] == null ? null:date('d-m-Y H:i', strtotime($this->attributes['email_verified_at']));
    }

    public function getPermissionArray()
    {
        return $this->getAllPermissions()->mapWithKeys(function ($pr) {
            return [$pr['name'] => true];
        });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    public function getProfileImageAttribute(): string
    {
        $url = $this->getFirstMediaUrl("profile_image");
        return $url ?: '/images/no_image.png';
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function getRole()
    {
        return $this->roles->first() ? $this->roles->first() : null;
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public static function approverOptions()
    {
        return self::where('id', '!=', auth()->id())
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
    }

}
