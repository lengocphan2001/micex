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

    /**
     * Check if deposit is under maintenance
     */
    public static function isDepositMaintenance()
    {
        $maintenance = self::getValue('deposit_maintenance', 'false');
        if ($maintenance === 'false' || empty($maintenance)) {
            return false;
        }

        $data = json_decode($maintenance, true);
        if (!$data || !isset($data['enabled']) || !$data['enabled']) {
            return false;
        }

        $now = now();
        $startDate = isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date']) : null;
        $endDate = isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date']) : null;

        if ($startDate && $endDate) {
            return $now->between($startDate, $endDate);
        }

        return false;
    }

    /**
     * Get deposit maintenance message
     */
    public static function getDepositMaintenanceMessage()
    {
        $maintenance = self::getValue('deposit_maintenance', 'false');
        if ($maintenance === 'false' || empty($maintenance)) {
            return null;
        }

        $data = json_decode($maintenance, true);
        if (!$data || !isset($data['message'])) {
            return 'Hệ thống nạp tiền đang bảo trì. Vui lòng thử lại sau.';
        }

        return $data['message'];
    }

    /**
     * Check if withdraw is under maintenance
     */
    public static function isWithdrawMaintenance()
    {
        $maintenance = self::getValue('withdraw_maintenance', 'false');
        if ($maintenance === 'false' || empty($maintenance)) {
            return false;
        }

        $data = json_decode($maintenance, true);
        if (!$data || !isset($data['enabled']) || !$data['enabled']) {
            return false;
        }

        $now = now();
        $startDate = isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date']) : null;
        $endDate = isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date']) : null;

        if ($startDate && $endDate) {
            return $now->between($startDate, $endDate);
        }

        return false;
    }

    /**
     * Get withdraw maintenance message
     */
    public static function getWithdrawMaintenanceMessage()
    {
        $maintenance = self::getValue('withdraw_maintenance', 'false');
        if ($maintenance === 'false' || empty($maintenance)) {
            return null;
        }

        $data = json_decode($maintenance, true);
        if (!$data || !isset($data['message'])) {
            return 'Hệ thống rút tiền đang bảo trì. Vui lòng thử lại sau.';
        }

        return $data['message'];
    }

    /**
     * Set deposit maintenance
     */
    public static function setDepositMaintenance($enabled, $startDate, $endDate, $message = null)
    {
        $data = [
            'enabled' => $enabled,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'message' => $message ?? 'Hệ thống nạp tiền đang bảo trì. Vui lòng thử lại sau.',
        ];

        return self::setValue('deposit_maintenance', json_encode($data), 'Deposit maintenance schedule');
    }

    /**
     * Set withdraw maintenance
     */
    public static function setWithdrawMaintenance($enabled, $startDate, $endDate, $message = null)
    {
        $data = [
            'enabled' => $enabled,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'message' => $message ?? 'Hệ thống rút tiền đang bảo trì. Vui lòng thử lại sau.',
        ];

        return self::setValue('withdraw_maintenance', json_encode($data), 'Withdraw maintenance schedule');
    }
}
