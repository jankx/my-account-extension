<?php
namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;
use Jankx\Extensions\MyAccount\Menu\AccountMenu;

class AccountMenuIconBlock extends Block
{
    protected $blockId = 'jankx/account-menu-icon';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $context = ($block instanceof \WP_Block && is_array($block->context)) ? $block->context : [];

        $slug = isset($context['jankx/menuSlug']) ? (string) $context['jankx/menuSlug'] : '';
        $entry = $slug !== '' ? AccountMenu::getEntry($slug) : null;

        $icon = $entry && $entry['icon'] !== '' ? $entry['icon'] : '';
        if ($icon === '' && !empty($context['jankx/menuIcon'])) {
            $icon = (string) $context['jankx/menuIcon'];
        }

        if ($icon === '') {
            return '';
        }

        return '<span class="jankx-nav-icon">' . $icon . '</span>';
    }
}
