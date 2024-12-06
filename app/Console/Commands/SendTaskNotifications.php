<?php

namespace App\Console\Commands;

use App\Mail\TaskNotificationMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaskNotifications extends Command
{
    protected $signature = 'tasks:send-notifications';

    protected $description = 'Send daily task notifications to users at 7 am';

    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            $tasks = $user->tasks()
                ->whereDate('start_date', '<=', Carbon::today())
                ->whereDate('end_date', '>=', Carbon::today())
                ->get();

            if ($tasks->isNotEmpty()) {
                Mail::to($user->email)
                    ->queue(new TaskNotificationMail($user, $tasks));

                $this->info("Email sent to {$user->email}");
            }
        }
    }
}
