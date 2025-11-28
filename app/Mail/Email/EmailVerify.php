<?php

namespace App\Mail\Email;

use App\Models\VendorUser;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailVerify extends Mailable implements ShouldQueue
{
//    use Queueable;
    use SerializesModels;

    public VendorUser $user;
    public string $code;

    /**
     * Create a new message instance.
     *
     * @param VendorUser $user
     * @param string $code
     * @return void
     */
    public function __construct(VendorUser $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this
            ->subject(trans('emails.verify_email.subject', ['otp' => $this->code]))
            ->view('emails.verify_email');
    }
}
