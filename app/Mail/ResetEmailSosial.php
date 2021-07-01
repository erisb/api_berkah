<?php
namespace App\Mail;
 
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
 
class ResetEmailSosial extends Mailable {
 
    use Queueable,
        SerializesModels;
 	
 	protected $id;
    public function __construct($id)
    {
        $this->id = $id;
    }

    //build the message.
    public function build() {
        return $this->view('email.reset_password_sosial')->with('data', $this->id);
    }
}