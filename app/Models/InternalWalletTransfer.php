<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalWalletTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id', 'to_user_id', 'from_balance', 'to_balance', 'amount', 'approval_status',
        'approved_by', 'approved_at', 'from_transaction_id', 'to_transaction_id',
        'remarks', 'request_by', 'company_id'
    ];

    // ✅ Renamed for clarity and Filament compatibility
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'request_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ✅ Ensure relationships are preloaded to prevent N+1 queries
    public function scopeWithRelations($query)
    {
        return $query->with(['fromUser', 'toUser', 'approver', 'requestor', 'company']);
    }

    // ✅ Handle permission check safely
    public static function canCreate(): bool
    {
        return auth()->check() && auth()->user()->hasRole('sales_operation_head');
    }

    public static function canAccess(): bool
    {
        return auth()->check() && (
            auth()->user()->hasRole('sales_operation_head') || auth()->user()->hasRole('accounts_head')
        );
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-fill from_balance & to_balance before creating
        static::creating(function ($transfer) {
            $transfer->from_balance = WalletLog::where('user_id', $transfer->from_user_id)
                ->orderBy('balance', 'desc')
                ->value('balance') ?? 0;

            $transfer->to_balance = WalletLog::where('user_id', $transfer->to_user_id)
                ->orderBy('balance', 'desc')
                ->value('balance') ?? 0;
        });
    }
}
