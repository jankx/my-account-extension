<?php
namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;
use Jankx\Extensions\MyAccount\Menu\AccountMenu;

class AccountMenuTextBlock extends Block
{
    protected $blockId = 'jankx/account-menu-text';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $context = ($block instanceof \WP_Block && is_array($block->context)) ? $block->context : [];

        $slug = isset($context['jankx/menuSlug']) ? (string) $context['jankx/menuSlug'] : '';
        $entry = $slug !== '' ? AccountMenu::getEntry($slug) : null;

        $label = $entry && $entry['label'] !== '' ? $entry['label'] : '';
        if ($label === '' && !empty($context['jankx/menuLabel'])) {
            $label = (string) $context['jankx/menuLabel'];
        }

        if ($label === '') {
            return '';
        }

        return '<span class="jankx-nav-label">' . esc_html($label) . '</span>';
    }
}
