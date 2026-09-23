<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-device key/value store (local SQLite — no server, no account).
 * Used for user preferences like the app language.
 */
class Setting extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::find($key)?->value ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
