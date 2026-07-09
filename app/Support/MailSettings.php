<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * Застосовує налаштування пошти (SMTP), задані в адмінці, поверх config/mail.
 * Якщо host не заданий — нічого не чіпаємо (лишається .env / драйвер log).
 * Пароль зберігається зашифрованим у таблиці settings.
 */
class MailSettings
{
    public static function apply(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $host = trim((string) Setting::get('mail_host'));
        if ($host === '') {
            return;
        }

        $encryption = Setting::get('mail_encryption') ?: 'ssl';

        $password = (string) Setting::get('mail_password');
        if ($password !== '') {
            try {
                $password = Crypt::decryptString($password);
            } catch (\Throwable) {
                // Якщо значення випадково не зашифроване — використовуємо як є.
            }
        }

        config([
            'mail.default'                 => 'smtp',
            'mail.mailers.smtp.host'       => $host,
            'mail.mailers.smtp.port'       => (int) (Setting::get('mail_port') ?: 465),
            'mail.mailers.smtp.username'   => Setting::get('mail_username') ?: null,
            'mail.mailers.smtp.password'   => $password !== '' ? $password : null,
            'mail.mailers.smtp.encryption' => $encryption === 'none' ? null : $encryption,
        ]);

        if ($from = trim((string) Setting::get('mail_from_address'))) {
            config(['mail.from.address' => $from]);
        }
        if ($fromName = trim((string) Setting::get('mail_from_name'))) {
            config(['mail.from.name' => $fromName]);
        }
    }
}
