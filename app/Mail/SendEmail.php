<?php

namespace App\Mail;

use App\Models\Sourcing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $sourcing;
    public $statusChanged;

    /**
     * Create a new message instance.
     *
     * @param  Sourcing  $sourcing
     * @return void
     */
    public function __construct(Sourcing $sourcing,$statusChanged)
    {
        $this->sourcing = $sourcing;
        $this->statusChanged = $statusChanged;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('admin@gmail.com', 'CODSQUAD TEAM')->subject('Sourcing Created')
                    ->view('emails.sourcing.created')
                    ->with([
                        'sourcingId' => $this->sourcing->id,
                        'productName' => $this->sourcing->product_name,
                        'productUrl' => $this->sourcing->product_url,
                        'statusChanged' => $this->statusChanged,
                    ]);
    }
}