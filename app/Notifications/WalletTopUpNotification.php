<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WalletTopUpNotification extends Notification
{
    use Queueable;

    protected $amount;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    
    public function __construct($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database']; // Use database channel only
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Wallet Top-Up Successful',
            'message' => 'Your wallet has been credited with an amount of ' . $this->amount . '.',
            'amount' => $this->amount,
        ];
    }
}
