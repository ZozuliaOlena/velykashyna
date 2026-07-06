<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Публічний прийом заявок із кошика клієнтської частини.
 * Гість (без реєстрації) надсилає контакти + список товарів,
 * ми створюємо заявку з фіксацією ціни на момент оформлення.
 */
class LeadController extends Controller
{
    // Телефон обов'язковий і має містити щонайменше 9 цифр (реальний номер),
    // щоб не оформлювали заявку з порожнім чи «+38».
    private const PHONE_RULES = ['required', 'string', 'max:255', 'regex:/(?:\D*\d){9,}/'];

    private const PHONE_MESSAGES = [
        'phone.required' => 'Вкажіть номер телефону.',
        'phone.regex'    => 'Вкажіть коректний номер телефону.',
    ];

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name'      => ['required', 'string', 'max:255'],
            'phone'              => self::PHONE_RULES,
            'contact_method'     => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:255'],
            'delivery_method'    => ['nullable', 'string', 'max:255'],
            'delivery_address'   => ['nullable', 'string', 'max:500'],
            'payment_method'     => ['nullable', 'string', 'max:255'],
            'comment'            => ['nullable', 'string', 'max:2000'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.qty'        => ['required', 'integer', 'min:1', 'max:1000'],
        ], self::PHONE_MESSAGES);

        // Доставку/оплату зберігаємо окремими полями (нижче), у коментар —
        // лише власне повідомлення клієнта.
        $customerComment = $data['comment'] ?? null;

        // Зведення дублікатів: один товар = один рядок із сумарною кількістю.
        $quantities = collect($data['items'])
            ->groupBy('product_id')
            ->map(fn ($rows) => $rows->sum('qty'));

        // Беремо лише активні товари; неактивні/неіснуючі — помилка валідації.
        $products = Product::where('is_active', true)
            ->whereIn('id', $quantities->keys())
            ->get()
            ->keyBy('id');

        $missing = $quantities->keys()->diff($products->keys());
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Деякі товари недоступні або зняті з продажу: '.$missing->implode(', '),
            ]);
        }

        $lead = DB::transaction(function () use ($data, $quantities, $products, $customerComment) {
            $lead = Lead::create([
                'customer_name'    => $data['customer_name'],
                'phone'            => $data['phone'],
                'contact_method'   => $data['contact_method'] ?? null,
                'city'             => $data['city'] ?? null,
                'delivery_method'  => $data['delivery_method'] ?? null,
                'delivery_address' => $data['delivery_address'] ?? null,
                'payment_method'   => $data['payment_method'] ?? null,
                'customer_comment' => $customerComment,
                'status'           => 'new',
                'source'           => 'cart',
            ]);

            foreach ($quantities as $productId => $qty) {
                $product = $products[$productId];
                $lead->items()->create([
                    'product_id'       => $product->id,
                    'qty'              => $qty,
                    // Ціна, яку бачив клієнт — у гривнях (валютні перераховані за курсом).
                    'price_at_request' => $product->priceUah(),
                ]);
            }

            return $lead;
        });

        return response()->json([
            'ok'          => true,
            'lead_id'     => $lead->id,
            'items_count' => $quantities->count(),
            'message'     => 'Замовлення прийнято. Ми зв’яжемося для підтвердження деталей.',
        ], 201);
    }

    /**
     * Заявка на консультацію (без кошика): ім'я, телефон і повідомлення.
     * Створює заявку з source='consultation' — в адмінці вона позначена
     * окремим типом «Консультація». Товарів не має.
     */
    public function consultation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone'         => self::PHONE_RULES,
            // Повідомлення клієнта (напр. «Що вас цікавить?»). Приймаємо і
            // 'comment', і 'message' — щоб форму було зручно підключити.
            'comment'       => ['nullable', 'string', 'max:2000'],
            'message'       => ['nullable', 'string', 'max:2000'],
        ], self::PHONE_MESSAGES);

        $lead = Lead::create([
            'customer_name'    => $data['customer_name'],
            'phone'            => $data['phone'],
            'customer_comment' => $data['comment'] ?? $data['message'] ?? null,
            'status'           => 'new',
            'source'           => 'consultation',
        ]);

        return response()->json([
            'ok'      => true,
            'lead_id' => $lead->id,
            'message' => 'Дякуємо! Ми отримали вашу заявку і зв’яжемося найближчим часом.',
        ], 201);
    }
}
