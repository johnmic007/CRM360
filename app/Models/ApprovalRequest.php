<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'company_id',
        'user_id',
        'school_id',
        'status'
    ];

    public function schoolUsers()
{
    return $this->hasMany(SchoolUser::class, 'school_id', 'school_id');
}



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }


    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }






}
