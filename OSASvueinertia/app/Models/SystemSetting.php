<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a system setting by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a system setting
     */
    public static function set($key, $value, $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }

    /**
     * Get coordinator name default
     */
    public static function getCoordinatorName()
    {
        return static::get('coordinator_name', '');
    }

    /**
     * Get director name default
     */
    public static function getDirectorName()
    {
        return static::get('director_name', '');
    }

    /**
     * Set coordinator name default
     */
    public static function setCoordinatorName($value)
    {
        return static::set('coordinator_name', $value, 'Default coordinator name for forms');
    }

    /**
     * Set director name default
     */
    public static function setDirectorName($value)
    {
        return static::set('director_name', $value, 'Default director name for forms');
    }

    /**
     * Check if image uploads are allowed
     */
    public static function allowImageUploads()
    {
        return (bool) static::get('allow_image_uploads', true);
    }

    /**
     * Set image upload setting
     */
    public static function setAllowImageUploads($value)
    {
        return static::set('allow_image_uploads', $value ? '1' : '0', 'Allow image uploads in List of Members and List of Officers forms');
    }
}
