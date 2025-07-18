<?php

namespace App\Traits;

trait HasSettings
{
    /**
     * Get the setting value for the given key.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * The settings that belong to the user.
     */
    public function settings(): array
    {
        return $this->settings;
    }

    /**
     * Set the given setting value for the given key.
     */
    public function setSetting(string $key, mixed $value): void
    {
        $settings = (array) $this->settings;

        $this->settings = array_merge($settings, [$key => $value]);

        $this->save();
    }
}
