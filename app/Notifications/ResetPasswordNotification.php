<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Permintaan Reset Password') // <== Ubah subject email
            ->greeting('Halo!')
            ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akun Anda.')
            ->action('Reset Password', url('/password/reset', $this->token) . '?email=' . urlencode($notifiable->getEmailForPasswordReset()))
            ->line('Tautan reset ini akan kedaluwarsa dalam 60 menit.')
            ->line('Jika Anda tidak meminta reset password, abaikan saja email ini.')
            ->salutation('Salam, Tracking Vektor'); // <== Ubah penutup email
    }
}
