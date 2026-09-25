<?php
namespace Jankx\Extensions\MyAccount\Menu;

use Jankx\Extensions\MyAccount\MyAccountExtension;

class AccountMenu
{
    public static function getAccountId(): int
    {
        return (int) get_option('jankx_my_account_page_id', 0);
    }

    public static function getBaseUrl(): string
    {
        $pageId = self::getAccountId();
        if (!$pageId) {
            return '#';
        }
        $url = get_permalink($pageId);
        return $url ? (string) $url : '#';
    }

    public static function getUrl(string $slug): string
    {
        $base = self::getBaseUrl();
        if ($base === '#' || $slug === '') {
            return '#';
        }
        return rtrim($base, '/') . '/' . $slug . '/';
    }

    public static function getActiveSlug(): string
    {
        $active = (string) get_query_var('jankx_account_page');

        if ($active === '') {
            $active = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : '';
        }

        return $active !== '' ? $active : 'overview';
    }

    public static function isActive(string $slug): bool
    {
        return $slug !== '' && $slug === self::getActiveSlug();
    }

    /**
     * All nav visible sub-pages keyed by slug.
     */
    public static function getEntries(): array
    {
        $entries = [];

        foreach (MyAccountExtension::getSubPages() as $slug => $page) {
            if (empty($page['show_in_nav'])) {
                continue;
            }
            $entries[(string) $slug] = self::buildEntry((string) $slug, $page);
        }

        return $entries;
    }

    /**
     * Single sub-page entry, null when the slug is unknown.
     */
    public static function getEntry(string $slug): ?array
    {
        if ($slug === '') {
            return null;
        }

        $pages = MyAccountExtension::getSubPages();
        if (!isset($pages[$slug])) {
            return null;
        }

        return self::buildEntry($slug, $pages[$slug]);
    }

    protected static function buildEntry(string $slug, array $page): array
    {
        return [
            'slug' => $slug,
            'label' => (string) ($page['label'] ?? ''),
            'icon' => (string) ($page['icon'] ?? ''),
            'url' => self::getUrl($slug),
            'active' => self::isActive($slug),
        ];
    }
}
