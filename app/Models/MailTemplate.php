<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailTemplate extends Model
{

    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'selected_users',
        'additional_emails',
    ];

    protected $casts = [
        'selected_users' => 'array', // Automatically decode/encode JSON for selected_users
    ];
}
