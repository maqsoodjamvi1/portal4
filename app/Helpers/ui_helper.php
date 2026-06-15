<?php

/**
 * UI asset flags for layouts/header.php (set in views or controllers).
 */
if (!function_exists('ui_asset_defaults')) {
    function ui_asset_defaults(array $overrides = []): array
    {
        return array_merge([
            'uiNeedsDataTables' => true,
            'uiNeedsSummernote' => false,
            'uiNeedsChart'      => false,
        ], $overrides);
    }
}
