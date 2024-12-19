<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'manager_id', // To define the reporting hierarchy
        'company_id',
        'allocated_districts',
        'allocated_blocks',
        'wallet_balance'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'allocated_districts' => 'array',
        'allocated_blocks' => 'array',
    ];

    /**
     * Define the relationship between the user and their roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->where('model_has_roles.model_type', self::class);
    }


    public function issuedBooks()
    {
        return $this->hasMany(IssuedBook::class, 'user_id');
    }

    public function leadStatuses()
    {
        return $this->hasMany(SalesLeadStatus::class, 'created_by');
    }
    

    public function     DealClosedBy
    ()
    {
        return $this->hasMany(Invoice::class, 'closed_by');
    }


    public function bookLogs()
    {
        return $this->hasMany(TestBookLog::class, 'user_id');
    }


    public function SchoolCopy()
    {
        return $this->hasMany(TestBookLog::class, 'created_by');
    }


    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function districts()
    {
        return $this->belongsToMany(District::class, 'districts', 'id', 'id', 'allocated_districts');
    }

    public function blocks()
    {
        return $this->belongsToMany(Block::class, 'blocks', 'id', 'id', 'allocated_blocks');
    }


    public function walletLogs()
    {
        return $this->hasMany(WalletLog::class);
    }


    // User.php model
    public function walletPaymentLogs()
    {
        return $this->hasMany(WalletPaymentLogs::class);
    }


    /**
     * Define the relationship for a user's subordinates (users they directly manage).
     */
    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * Define the relationship for the user's manager (their direct superior).
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Recursively fetch all subordinates for the current user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function allSubordinates()
    {
        return $this->subordinates()->with('allSubordinates')->get();
    }

    /**
     * Scope to get all users viewable by the current user.
     * - Admin: Can see all users.
     * - Others: Can see themselves and their direct/indirect subordinates recursively.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeViewableBy($query, User $user)
    {
        // Check if the user has the 'Admin' role
        if ($user->roles()->where('name', 'Admin')->exists()) {
            return $query; // Admin can view all users
        }

        // Fetch all subordinates recursively
        $subordinateIds = $user->getAllSubordinateIds();

        dd($subordinateIds);

        return $query->whereIn('id', $subordinateIds); // Include all subordinates
    }

    /**
     * Get all subordinate IDs for the current user, including recursive subordinates.
     *
     * @return array<int>
     */
    // app/Models/User.php




    public function getAllSubordinateIds()
    {
        $subordinatesIds = [];

        // Initialize the list of subordinates starting with direct subordinates.
        $subordinates = $this->subordinates()->pluck('id')->toArray();

        // Loop through each direct subordinate and collect their subordinates recursively
        $allSubordinates = collect($subordinates);  // Start with direct subordinates.

        while ($subordinates) {
            // Fetch subordinates of the subordinates in the current loop
            $newSubordinates = User::whereIn('manager_id', $subordinates)->pluck('id')->toArray();

            if (empty($newSubordinates)) {
                break;  // If no new subordinates, break out of the loop.
            }

            // Merge new subordinates into the list and continue the loop
            $allSubordinates = $allSubordinates->merge($newSubordinates);

            // Update the subordinates for the next iteration
            $subordinates = $newSubordinates;
        }

        return $allSubordinates->toArray();
    }
}
