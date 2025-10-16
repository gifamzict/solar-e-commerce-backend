<?php

namespace App\Mail;

use App\Models\CustomerPreOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerPreOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CustomerPreOrder $customerPreOrder,
        public string $subject,
        public string $resolvedMessage,
        public array $notificationData
    ) {}

    public function build()
    {
        $brandName = config('app.brand_name', 'G-Tech Solar');
        
        return $this->view('emails.customer-preorder-notification')
                    ->subject($this->subject)
                    ->from(config('mail.from.address'), $brandName)
                    ->with([
                        'customerPreOrder' => $this->customerPreOrder,
                        'resolvedMessage' => $this->resolvedMessage,
                        'notificationData' => $this->notificationData,
                        'brandName' => $brandName,
                    ]);
    }
}