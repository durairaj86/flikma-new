<?php

namespace App\Support;

class ShortCuts
{
    /**
     * Common shortcuts shared across all modules
     */
    protected static function common(): array
    {
        return [
            'general' => [
                'Alt+S' => 'Save',
                'Esc' => 'Close',
                'Ctrl+/' => 'Shortcut Open/Close',
                'F3' => 'Nav Tab'
            ],
            'navigation' => [
                'Tab' => 'Next field',
                'Shift+Tab' => 'Previous field',
                '←' => 'Move left',
                '→' => 'Move right',
                '↑' => 'Move up',
                '↓' => 'Move down',
            ],

        ];
    }

    /**
     * Banks module
     */
    protected static function banks(): array
    {
        return [
            'function_keys' => [
                'F1' => 'Currency Modal Close',
            ],
        ];
    }

    /**
     * Customers module
     */
    protected static function customers(): array
    {
        return [
            'navigation' => [
                'Alt+N' => 'New Customer',
                'Alt+E' => 'Edit Customer',
            ],
            'function_keys' => [
                'F2' => 'Customer Edit',
            ],
        ];
    }

    /**
     * Suppliers module
     */
    protected static function suppliers(): array
    {
        return [
            'navigation' => [
                'Alt+N' => 'New Supplier',
                'Alt+E' => 'Edit Supplier',
            ],
            'function_keys' => [
                'F2' => 'Supplier Edit',
            ],
        ];
    }

    /**
     * Jobs module
     */
    protected static function jobs(): array
    {
        return [
            'navigation' => [
                'Alt+N' => 'New Job',
                'Alt+E' => 'Edit Job',
            ],
            'function_keys' => [
                'F2' => 'Job Edit',
            ],
        ];
    }

    /**
     * Get shortcuts for a module (merged with common)
     */
    public static function get(string $module = null): array
    {
        $shortcuts = self::common();

        // dynamically call module method if it exists
        if ($module && method_exists(__CLASS__, $module)) {
            $moduleShortcuts = self::$module(); // call e.g. self::banks()
            foreach ($moduleShortcuts as $section => $items) {
                if (isset($shortcuts[$section])) {
                    $shortcuts[$section] = array_merge($shortcuts[$section], $items);
                } else {
                    $shortcuts[$section] = $items;
                }
            }
        }

        return $shortcuts;
    }

    /**
     * List all modules
     */
    public static function allModules(): array
    {
        return ['banks', 'customers', 'suppliers', 'jobs'];
    }
}
