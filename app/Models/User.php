<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_GUARD = 'guard';
    public const ROLE_STUDENT_REPRESENTATIVE = 'student_representative';
    public const ROLE_PURCHASER = 'purchaser';
    public const ROLE_LIBRARIAN = 'librarian';
    public const ROLE_COOK = 'cook';
    public const ROLE_DORM_STUDENT = 'dorm_student';
    public const ROLE_LIBRARY_MEMBER = 'library_member';
    public const ROLE_APPLICANT = 'applicant';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'status',
        'theme',
        'profile_photo_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_OWNER => 'صاحب اصلی لیلیه',
            self::ROLE_MANAGER => 'مدیر لیلیه',
            self::ROLE_ADMIN => 'ادمین سیستم',
            self::ROLE_GUARD => 'گارد',
            self::ROLE_STUDENT_REPRESENTATIVE => 'نماینده محصلین',
            self::ROLE_PURCHASER => 'خرج‌آور',
            self::ROLE_LIBRARIAN => 'کتاب‌دار',
            self::ROLE_COOK => 'آشپز',
            self::ROLE_DORM_STUDENT => 'محصل لیلیه',
            self::ROLE_LIBRARY_MEMBER => 'عضو کتاب‌خانه',
            self::ROLE_APPLICANT => 'متقاضی عضویت',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'در انتظار تایید',
            self::STATUS_ACTIVE => 'فعال',
            self::STATUS_SUSPENDED => 'مسدود',
        ];
    }

    public static function managementRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN,
        ];
    }

    public static function financeAdminRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
        ];
    }

    public static function dormRecordViewerRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN,
            self::ROLE_STUDENT_REPRESENTATIVE,
            self::ROLE_PURCHASER,
            self::ROLE_GUARD,
            self::ROLE_COOK,
        ];
    }

    public static function studentRepresentativeRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN,
            self::ROLE_STUDENT_REPRESENTATIVE,
        ];
    }

    public static function purchaserRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN,
            self::ROLE_PURCHASER,
        ];
    }

    public static function libraryViewerRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN,
            self::ROLE_LIBRARIAN,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function canAccessAdmin(): bool
    {
        return $this->isActive() && in_array($this->role, self::managementRoles(), true);
    }

    public function canManageUsers(): bool
    {
        return $this->canAccessAdmin();
    }

    public function canManageFinance(): bool
    {
        return $this->isActive() && in_array($this->role, self::financeAdminRoles(), true);
    }

    public function manageableRoleOptions(): array
    {
        $roles = self::roleOptions();

        if ($this->role !== self::ROLE_OWNER) {
            unset($roles[self::ROLE_OWNER], $roles[self::ROLE_MANAGER]);
        }

        if ($this->role === self::ROLE_ADMIN) {
            unset($roles[self::ROLE_ADMIN]);
        }

        return $roles;
    }
}
