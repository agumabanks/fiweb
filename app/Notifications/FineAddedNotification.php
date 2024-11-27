<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ClientFine;

class FineAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $fine;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\ClientFine  $fine
     * @return void
     */
    public function __construct(ClientFine $fine)
    {
        $this->fine = $fine;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail']; // You can add other channels like 'database' if needed
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Fine Added to Your Account')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('A new fine has been added to your account.')
                    ->line('**Amount:** UGX ' . number_format($this->fine->amount, 2))
                    ->line('**Reason:** ' . $this->fine->reason)
                    ->line('**Note:** ' . ($this->fine->note ?? 'N/A'))
                    ->line('If you have any questions, please contact our support team.')
                    ->salutation('Regards, Your Company Name');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable)
    {
        return [
            'fine_id' => $this->fine->id,
            'amount'  => $this->fine->amount,
            'reason'  => $this->fine->reason,
            'note'    => $this->fine->note,
            'added_by'=> $this->fine->addedBy->name ?? 'Admin',
        ];
    }
}
