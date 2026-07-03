<?php

// app/Models/Setting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    /**
     * Пам'ять у межах одного запиту: усі налаштування читаються з БД один раз,
     * а не окремим запитом на кожен get(). Скидається при збереженні/видаленні.
     */
    protected static ?array $memo = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::$memo = null);
        static::deleted(fn () => static::$memo = null);
    }

    protected static function allValues(): array
    {
        return static::$memo ??= static::query()->pluck('value', 'key')->all();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::allValues()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$memo = null;
    }
}
