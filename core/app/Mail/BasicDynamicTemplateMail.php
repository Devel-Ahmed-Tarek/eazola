<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BasicDynamicTemplateMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $type;

    public function __construct($args, $type = null)
    {
        $this->data = $args;
        $this->type = $type;
    }
    
    public function build()
    {

        if (isset($this->data['type']) && in_array($this->data['type'],['admin_subscription','user_subscription'])) {

            $payment_details = $this->data['logs'] ?? [];

            $mailable = $this->from(
                get_static_option('site_global_email'),
                get_static_option('site_' . get_user_lang() . '_title')
            )
                ->subject($this->data['subject'])
                ->markdown('emails.basic-dynamic-template');

            if (class_exists(\Barryvdh\DomPDF\Facade::class)) {
                $invoice_details = \Barryvdh\DomPDF\Facade::loadView('landlord.frontend.invoice.package-order', compact('payment_details'));

                $invoice_details->setPaper('L');
                $invoice_details->output();
                $canvas = $invoice_details->getDomPDF()->getCanvas();

                $height = $canvas->get_height();
                $width = $canvas->get_width();

                $canvas->set_opacity(.2, "Multiply");
                $canvas->set_opacity(.2);
                $canvas->page_text($width / 5, $height / 2, __('Paid'), null, 55, array(0, 0, 0), 2, 2, -30);

                return $mailable->attachData($invoice_details->output(), "invoice.pdf");
            }

            Log::warning('DomPDF facade not found. Sending email without invoice PDF.', [
                'mail_type' => $this->data['type'] ?? null,
            ]);

            return $mailable;

        }else{

            return $this->from(get_static_option('site_global_email'), get_static_option('site_' . get_user_lang() . '_title'))
                ->subject($this->data['subject'])
                ->markdown('emails.basic-dynamic-template');
        }


    }


}
