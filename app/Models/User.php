<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'pin',
        'role',
        'status',
        'approval_limit',
        'last_login_at',
        'created_by',
        'profile_completed',
        'customer_id',
        'registration_fee_paid',
        'registration_fee_amount',
        'registration_fee_paid_at',
        'accepted_terms',
        'accepted_terms_at',
        'accepted_terms_version',
        'accepted_terms_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'pin',
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
            'pin' => 'hashed',
            'profile_completed' => 'boolean',
            'registration_fee_paid' => 'boolean',
            'registration_fee_paid_at' => 'datetime',
            'accepted_terms' => 'boolean',
            'accepted_terms_at' => 'datetime',
            'last_login_at' => 'datetime',
            'approval_limit' => 'decimal:2',
        ];
    }

    /**
     * Role constants
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_CLERK = 'clerk';
    const ROLE_COLLECTOR = 'collector';
    const ROLE_CUSTOMER = 'customer';

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    /**
     * Check if user is clerk
     */
    public function isClerk(): bool
    {
        return $this->role === self::ROLE_CLERK;
    }

    /**
     * Check if user is collector
     */
    public function isCollector(): bool
    {
        return $this->role === self::ROLE_COLLECTOR;
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Check if user is staff (any staff role)
     */
    public function isStaff(): bool
    {
        return in_array($this->role, [
            self::ROLE_SUPER_ADMIN,
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_CLERK,
            self::ROLE_COLLECTOR,
        ]);
    }

    /**
     * Check if user has permission level (role hierarchy)
     */
    public function hasRoleLevel(string $role): bool
    {
        $hierarchy = [
            self::ROLE_SUPER_ADMIN => 5,
            self::ROLE_ADMIN => 4,
            self::ROLE_MANAGER => 3,
            self::ROLE_CLERK => 2,
            self::ROLE_COLLECTOR => 1,
            self::ROLE_CUSTOMER => 0,
        ];

        $userLevel = $hierarchy[$this->role] ?? 0;
        $requiredLevel = $hierarchy[$role] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if user can approve loan amount
     */
    public function canApproveLoan(float $amount): bool
    {
        // Super admin and admin can approve any amount
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        // Manager can approve up to their limit
        if ($this->isManager() && $this->approval_limit !== null) {
            return $amount <= $this->approval_limit;
        }

        return false;
    }

    /**
     * Get the customer associated with this user
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /**
     * Get the registration fee associated with this user
     */
    public function registrationFee()
    {
        return $this->hasOne(\App\Models\RegistrationFee::class);
    }

    /**
     * Get the user who created this user
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get users created by this user
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }
}
