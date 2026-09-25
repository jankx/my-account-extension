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

        return sprintf(
            '<li class="%s"><a href="%s" class="jankx-nav-link">%s</a></li>',
            esc_attr($classes),
            esc_url($url),
            $content
        );
    }
}
