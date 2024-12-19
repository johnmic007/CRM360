<?php

namespace App\Filament\Resources\MailLogResource\Pages;

use App\Filament\Resources\MailLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use App\Models\MailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CreateMailLog extends CreateRecord
{
    protected static string $resource = MailLogResource::class;

    protected function afterCreate(): void
    {
        $data = $this->record; // Get the created record
    
        try {
            // Handle "to" and "cc" emails
            $toEmails = is_string($data->to_emails) && str_contains($data->to_emails, ',') 
                ? array_filter(explode(',', $data->to_emails)) 
                : $data->to_emails;
    
            $ccEmails = is_string($data->cc_emails) && str_contains($data->cc_emails, ',') 
                ? array_filter(explode(',', $data->cc_emails)) 
                : $data->cc_emails;
    
            // Check if there are valid "to" emails
            if (empty($toEmails)) {
                $data->update(['status' => 'failed']);
                Log::warning('No valid "to" email addresses provided.');
                return;
            }
    
            // Email subject and content
            $subject = MailTemplate::find($data->mail_template_id)?->name ?? 'New Mail';
            $content = $data->content;
    
            // Send the email
            Mail::send([], [], function ($message) use ($toEmails, $ccEmails, $subject, $content) {
                $message->to($toEmails);
    
                if (!empty($ccEmails)) {
                    $message->cc($ccEmails);
                }
    
                $message->subject($subject);
                $message->html($content); // Set the email body as HTML
            });
    
            // Update status to 'sent'
            $data->update(['status' => 'sent']);
            Log::info('Mail sent successfully.', ['to' => $toEmails, 'cc' => $ccEmails]);
    
        } catch (\Exception $e) {
            // Update status to 'failed' on exception
            $data->update(['status' => 'failed']);
    
            Log::error('Mail sending failed: ' . $e->getMessage(), [
                'to' => $data->to_emails,
                'cc' => $data->cc_emails,
                'record_id' => $data->id,
            ]);
        }
    }
    
    
    
}
