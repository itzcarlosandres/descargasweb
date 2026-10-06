<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Get one or all site settings.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return Setting::getAll();
        }

        return Setting::get($key, $default);
    }
}
