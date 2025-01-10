<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reimbursement extends Model
{
    protected $fillable = [
        'trainer_visit_id',
        'user_id',
        'company_id',
        'amount_due',
        'amount_covered',
        'amount_remaining',
        'status',
        'notes',
    ];

    /**
     * The TrainerVisit this reimbursement is associated with.
     */
    public function trainerVisit(): BelongsTo
    {
        return $this->belongsTo(TrainerVisit::class);
    }

    /**
     * The user who is to be reimbursed (or owes the remainder).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically assign company_id before creating a reimbursement
        static::creating(function ($reimbursement) {
            if ($reimbursement->user) {
                $reimbursement->company_id = $reimbursement->user->company_id;
            }
        });
    }
}
