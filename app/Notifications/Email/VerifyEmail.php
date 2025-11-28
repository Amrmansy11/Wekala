<?php

namespace App\Notifications\Email;

use App\Contracts\Notification\Notification as BaseNotification;
use App\Models\VendorUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Mail\Email\EmailVerify;

class VerifyEmail extends Notification implements BaseNotification, ShouldQueue
{
    use Queueable;

    protected VendorUser $user;
    protected string $code;

    /**
     * Create a new notification instance.
     * @param VendorUser $user
     * @param string $code
     * @return void
     */
    public function __construct(VendorUser $user, string $code)
    {
        $this->afterCommit();
        $this->queue = 'notifications';
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        $via[] = 'mail';
        return $via;
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return EmailVerify
     */
    public function toMail(mixed $notifiable): EmailVerify
    {
        return (new EmailVerify($this->user, $this->code))->to($notifiable->email);
    }


    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [];
    }

    /**
     * Return Notification Message
     *
     * @param array $data
     * @return string
     */
    public static function getMessage(array $data): string
    {
        return '';
    }


    /**
     * Return Notification Icon URL
     *
     * @param array $data
     * @return string
     */
    public static function getIconURL(array $data = []): string
    {
        return '';
    }

    /**
     * Return Notification Route Name
     *
     * @param array $data
     * @return string
     */
    public static function getRouteName(array $data = []): string
    {
        return '';
    }

}
