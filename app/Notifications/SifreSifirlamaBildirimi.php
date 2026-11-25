<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class SifreSifirlamaBildirimi extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        $dakika = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('Cardino — Şifre sıfırlama')
            ->greeting('Merhaba,')
            ->line('Cardino hesabınız için bir şifre sıfırlama isteği aldık.')
            ->action('Şifreyi sıfırla', $url)
            ->line("Bu bağlantı {$dakika} dakika boyunca geçerlidir.")
            ->line('Bu isteği siz yapmadıysanız bu e-postayı yok sayabilirsiniz; şifreniz değişmez.')
            ->salutation('Cardino');
    }
}
