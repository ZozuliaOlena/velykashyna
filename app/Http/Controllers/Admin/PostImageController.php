<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Приймає зображення, вставлені прямо в текст статті (редактор Trix),
 * зберігає у public-диск і повертає URL для вставки в контент.
 */
class PostImageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'],
        ]);

        try {
            $file = $request->file('file');
            $name = Str::random(24) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('blog', $name, 'public');

            // Корене-відносний URL (/storage/...), щоб працювало незалежно від хоста.
            return response()->json([
                'url' => MediaUrl::rel(Storage::disk('public')->url($path)),
            ]);
        } catch (\Throwable $e) {
            // Редактор покаже це повідомлення тостом замість «сирої» помилки.
            report($e);

            return response()->json(
                ['message' => 'Не вдалося зберегти зображення. Спробуйте ще раз.'],
                500,
            );
        }
    }
}
