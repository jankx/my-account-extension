<?php
namespace Jankx\Extensions\MyAccount;

use Jankx\Extensions\MyAccount\Menu\AccountMenu;

class SidebarNavBlock extends Block
{
    protected $blockId = 'jankx/sidebar-nav';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-sidebar-nav',
        ]);

        $hasInnerBlocks = $block instanceof \WP_Block
            && !empty($block->parsed_block['innerBlocks']);

        if ($hasInnerBlocks) {
            return sprintf('<nav %s><ul class="jankx-nav-list">%s</ul></nav>', $wrapperAttrs, $content);
        }

        $entries = AccountMenu::getEntries();
        if (empty($entries)) {
            return '';
        }

        $output = sprintf('<nav %s>', $wrapperAttrs);
        $output .= '<ul class="jankx-nav-list">';

        foreach ($entries as $entry) {
            $classes = 'jankx-nav-item' . ($entry['active'] ? ' jankx-nav-active' : '');

            $output .= '<li class="' . esc_attr($classes) . '">';
            $output .= '<a href="' . esc_url($entry['url']) . '" class="jankx-nav-link">';
            if ($entry['icon'] !== '') {
                $output .= '<span class="jankx-nav-icon">' . $entry['icon'] . '</span>';
            }
            $output .= '<span class="jankx-nav-label">' . esc_html($entry['label']) . '</span>';
            $output .= '</a></li>';
        }

        $output .= '</ul></nav>';
        return $output;
    }
}
