<?php

namespace App\Mail;

use App\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorreoUsuario extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;


    public  $password;
    public  $usuario_destino;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Usuario $usuario,$pass)
    {
        $this->password=$pass;
        $this->usuario_destino=$usuario;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Bievenido a Assistance")->view('email');
    }
}
