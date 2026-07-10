<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Сповіщення у Telegram про нові заявки з сайту (кошик і консультації).
 * Токен бота й список ID отримувачів задаються в адмінці (Налаштування).
 */
class TelegramNotifier
{
    /** Надіслати сповіщення про нову заявку всім заданим отримувачам. */
    public function notifyNewLead(Lead $lead): void
    {
        $token = trim((string) Setting::get('telegram_bot_token'));
        $ids = $this->chatIds();

        if ($token === '' || $ids->isEmpty()) {
            return;
        }

        $text = $this->buildMessage($lead);

        foreach ($ids as $chatId) {
            $this->send($token, $chatId, $text);
        }
    }

    /** Тестове повідомлення (кнопка «Надіслати тест» в адмінці). */
    public function sendTest(): int
    {
        $token = trim((string) Setting::get('telegram_bot_token'));
        $ids = $this->chatIds();

        if ($token === '' || $ids->isEmpty()) {
            return 0;
        }

        $sent = 0;
        foreach ($ids as $chatId) {
            if ($this->send($token, $chatId, "✅ <b>Тест</b>\nСповіщення про заявки налаштовано правильно.")) {
                $sent++;
            }
        }

        return $sent;
    }

    /** ID отримувачів (через кому/пробіл/новий рядок), унікальні. */
    private function chatIds()
    {
        return collect(preg_split('/[\s,;]+/', (string) Setting::get('telegram_chat_ids')))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->unique()
            ->values();
    }

    private function send(string $token, string $chatId, string $text): bool
    {
        try {
            $res = Http::timeout(5)->asForm()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if (! $res->successful()) {
                // Не ламаємо оформлення заявки - лише пишемо в лог.
                logger()->warning('Telegram sendMessage failed', ['chat_id' => $chatId, 'response' => $res->body()]);
            }

            return $res->successful();
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private function buildMessage(Lead $lead): string
    {
        $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        // Рядок «підпис: значення», лише якщо значення не порожнє.
        $row = fn (string $label, ?string $val) => trim((string) $val) !== ''
            ? "{$label} <b>{$e($val)}</b>" : null;

        if ($lead->source === 'consultation') {
            $lines = [
                "💬 <b>Нова консультація</b> #{$lead->id}",
                '',
                $row('👤 Ім’я:', $lead->customer_name),
                $row('📞 Телефон:', $lead->phone),
                $row('📝 Повідомлення:', $lead->customer_comment),
            ];
        } else {
            $lead->loadMissing('items.product');
            $total = $lead->items->sum(fn ($i) => (float) ($i->price_at_request ?? 0) * (int) $i->qty);

            $lines = [
                "🛒 <b>Нова заявка з кошика</b> #{$lead->id}",
                '',
                $row('👤 Ім’я:', $lead->customer_name),
                $row('📞 Телефон:', $lead->phone),
                '',
                "🛞 <b>Товари ({$lead->items->count()}):</b>",
            ];

            foreach ($lead->items->take(20) as $i) {
                $name = $i->product?->name ?? ('#' . $i->product_id);
                $sku = $i->product?->sku;
                // Спершу артикул (у <code> - копіюється по тапу в Telegram), потім назва.
                $prefix = $sku ? "<code>{$e($sku)}</code> - " : '';
                $lines[] = "   • {$prefix}{$e($name)} - {$i->qty} шт";
            }

            $lines[] = $total > 0
                ? '💰 Сума: <b>' . number_format($total, 0, '.', ' ') . ' грн</b>'
                : '💰 Сума: <b>уточнюється</b>';

            $lines[] = '';
            $lines[] = $row('🚚 Доставка:', $lead->delivery_method);
            $lines[] = $row('🏙 Місто:', $lead->city);
            $lines[] = $row('🏤 Відділення / адреса:', $lead->delivery_address);
            $lines[] = $row('💳 Оплата:', $lead->payment_method);
            $lines[] = $row('📝 Коментар:', $lead->customer_comment);
        }

        $lines[] = '';
        $lines[] = '🔗 ' . route('admin.leads.index');

        // Прибираємо порожні (null) рядки від умовних полів, зайві пусті - лишаємо для розділення.
        return implode("\n", array_filter($lines, fn ($l) => $l !== null));
    }
}
