<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        try {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        } catch (\Exception $e) {
            // If table doesn't exist yet, return default value
            return $default;
        }
    }

    /**
     * Set setting value by key
     */
    public static function setValue(string $key, string $value, string $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Get VND to Gem rate
     */
    public static function getVndToGemRate()
    {
        $rate = self::getValue('vnd_to_gem_rate', '1000');
        return (float) $rate;
    }
}
