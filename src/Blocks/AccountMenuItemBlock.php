<?php
namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;
use Jankx\Extensions\MyAccount\Menu\AccountMenu;

class AccountMenuItemBlock extends Block
{
    protected $blockId = 'jankx/account-menu-item';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $slug = sanitize_key($attributes['slug'] ?? '');
        $entry = AccountMenu::getEntry($slug);

        if (!$slug && !$entry) {
            return '';
        }

        $label = $entry ? $entry['label'] : (string) ($attributes['label'] ?? '');
        $icon = $entry ? $entry['icon'] : (string) ($attributes['icon'] ?? '');
        $url = $entry ? $entry['url'] : AccountMenu::getUrl($slug);

        if ($content === '' || $content === null) {
            $content = ($icon !== '' ? '<span class="jankx-nav-icon">' . $icon . '</span>' : '')
                . ($label !== '' ? '<span class="jankx-nav-label">' . esc_html($label) . '</span>' : '');
        }

        $classes = 'jankx-nav-item';
        if ($entry && $entry['active']) {
            $classes .= ' jankx-nav-active';
        }

        $gap = $this->resolveBlockGap($attributes);
        $linkAttrs = $gap !== '' ? sprintf(' style="gap:%s"', esc_attr($gap)) : '';

        return sprintf(
            '<li class="%s"><a href="%s" class="jankx-nav-link"%s>%s</a></li>',
            esc_attr($classes),
            esc_url($url),
            $linkAttrs,
            $content
        );
    }

    protected function resolveBlockGap(array $attributes): string
    {
        $gap = $attributes['style']['spacing']['blockGap'] ?? null;
        if (empty($gap)) {
            return '';
        }

        $gap = wp_sanitize_block_gap_value($gap);
        if (empty($gap)) {
            return '';
        }

        if (is_array($gap)) {
            $gap = implode(' ', array_values(array_filter(array_map(function ($value) {
                return is_string($value) ? $this->resolveGapValue($value) : '';
            }, $gap))));
        } else {
            $gap = $this->resolveGapValue($gap);
        }

        return $gap;
    }

    protected function resolveGapValue(string $value): string
    {
        if (str_contains($value, 'var:preset|spacing|')) {
            $slug = substr($value, (int) strrpos($value, '|') + 1);
            return 'var(--wp--preset--spacing--' . strtolower($slug) . ')';
        }

        return $value;
    }
}
