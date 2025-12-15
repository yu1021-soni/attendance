<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailJa extends VerifyEmail
{
    /**
     * メール本文を日本語にする
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('【要確認】メールアドレスの確認')
            ->greeting('こんにちは！') // "Hello!"
            ->line('以下のボタンをクリックして、メールアドレスの確認を完了してください。') // 説明文
            ->action('メールアドレスを確認する', $verificationUrl) // ボタン文言
            ->line('もしこのアカウントを作成していない場合は、このメールは破棄してください。') // 注意文
            ->salutation('よろしくお願いします'); // "Regards"
    }
}
