<?php
class Setting
{
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (Db::all('SELECT setting_key, setting_value FROM settings') as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = self::load()[$key] ?? null;
        return ($value === null || $value === '') ? $default : $value;
    }

    public static function all(): array
    {
        return self::load();
    }

    public static function set(string $key, ?string $value): void
    {
        Db::run(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, $value]
        );
        self::$cache[$key] = $value;
    }

    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::set($key, $value === null ? null : (string) $value);
        }
    }
}
