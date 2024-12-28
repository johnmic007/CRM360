<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mail_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'to_emails',
        'cc_emails',
        'company_id',
        'mail_template_id',
        'subject',
        'content',
        'status',
        'error_message',
    ];

    /**
     * Get the user who sent the email.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function mail_template(): BelongsTo
    {
        return $this->belongsTo(MailTemplate::class);
    }

    /**
     * Set to_emails as a comma-separated string.
     */
    public function setToEmailsAttribute($value): void
    {
        $this->attributes['to_emails'] = is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * Get to_emails as an array.
     */
    public function getToEmailsAttribute($value): array
    {
        return explode(',', $value);
    }

    /**
     * Set cc_emails as a comma-separated string.
     */
    public function setCcEmailsAttribute($value): void
    {
        $this->attributes['cc_emails'] = is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * Get cc_emails as an array.
     */
    public function getCcEmailsAttribute($value): ?array
    {
        return $value ? explode(',', $value) : null;
    }
}
