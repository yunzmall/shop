<?php

/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/2/22
 * Time: 16:03
 */

namespace app\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderInvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->data['send_email'])->view('invoice.goEmail', ['data' => $this->data]);
    }
}
