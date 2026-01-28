<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMenuSetting extends Model
{
    use HasFactory;

    /**
     * The database connection that should be used by the model.
     * This table is in the central/landlord database, not tenant database.
     *
     * @var string
     */
    protected $connection = 'mysql';

    protected $table = 'tenant_menu_settings';

    protected $fillable = [
        'tenant_id',
        'menu_key',
        'menu_label',
        'parent_key',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /**
     * Get the tenant that owns this menu setting.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    /**
     * Check if a menu item is visible for a specific tenant.
     * Returns true if no setting exists (default visible) or if is_visible = true.
     *
     * @param string $tenantId
     * @param string $menuKey
     * @return bool
     */
    public static function isMenuVisible(string $tenantId, string $menuKey): bool
    {
        $setting = self::where('tenant_id', $tenantId)
                       ->where('menu_key', $menuKey)
                       ->first();

        // If no setting exists, menu is visible by default
        if (!$setting) {
            return true;
        }

        return $setting->is_visible;
    }

    /**
     * Check if multiple menu items are visible for a specific tenant.
     * Returns an array of menu_key => is_visible.
     *
     * @param string $tenantId
     * @param array $menuKeys
     * @return array
     */
    public static function getMenuVisibility(string $tenantId, array $menuKeys): array
    {
        $settings = self::where('tenant_id', $tenantId)
                        ->whereIn('menu_key', $menuKeys)
                        ->pluck('is_visible', 'menu_key')
                        ->toArray();

        // Fill in defaults for missing keys (visible by default)
        $result = [];
        foreach ($menuKeys as $key) {
            $result[$key] = $settings[$key] ?? true;
        }

        return $result;
    }

    /**
     * Get all hidden menu keys for a tenant.
     *
     * @param string $tenantId
     * @return array
     */
    public static function getHiddenMenus(string $tenantId): array
    {
        return self::where('tenant_id', $tenantId)
                   ->where('is_visible', false)
                   ->pluck('menu_key')
                   ->toArray();
    }

    /**
     * Set menu visibility for a tenant.
     *
     * @param string $tenantId
     * @param string $menuKey
     * @param bool $isVisible
     * @param string|null $menuLabel
     * @param string|null $parentKey
     * @return self
     */
    public static function setMenuVisibility(
        string $tenantId,
        string $menuKey,
        bool $isVisible,
        ?string $menuLabel = null,
        ?string $parentKey = null
    ): self {
        return self::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'menu_key' => $menuKey,
            ],
            [
                'is_visible' => $isVisible,
                'menu_label' => $menuLabel,
                'parent_key' => $parentKey,
            ]
        );
    }

    /**
     * Bulk update menu visibility for a tenant.
     *
     * @param string $tenantId
     * @param array $menuSettings Array of ['menu_key' => bool, ...]
     * @return void
     */
    public static function bulkSetVisibility(string $tenantId, array $menuSettings): void
    {
        foreach ($menuSettings as $menuKey => $isVisible) {
            self::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'menu_key' => $menuKey,
                ],
                [
                    'is_visible' => $isVisible,
                ]
            );
        }
    }

    /**
     * Reset all menu settings for a tenant (delete all settings, revert to defaults).
     *
     * @param string $tenantId
     * @return int Number of deleted records
     */
    public static function resetToDefault(string $tenantId): int
    {
        return self::where('tenant_id', $tenantId)->delete();
    }
}
