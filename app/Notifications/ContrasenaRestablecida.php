<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContrasenaRestablecida extends Notification
{
    use Queueable;

    public function __construct(private readonly bool $sesionesCerradas)
    {
    }

    /** Envía el aviso por correo usando el transporte configurado en Laravel. */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $correo = (new MailMessage())
            ->subject('Se cambió la contraseña de tu cuenta Chaskayni')
            ->greeting('Hola, '.$notifiable->name)
            ->line('La contraseña de tu cuenta Chaskayni se cambió correctamente.');

        if ($this->sesionesCerradas) {
            $correo->line('Por seguridad, cerramos las sesiones activas de tu cuenta.');
        } else {
            $correo->line('Por seguridad, revisa tus dispositivos y cierra cualquier sesión que no reconozcas.');
        }

        return $correo
            ->line('Si no hiciste este cambio, solicita otro restablecimiento y comunícate con administración.')
            ->action('Ir a iniciar sesión', route('login'))
            ->salutation('Equipo Chaskayni');
    }
}