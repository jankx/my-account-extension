<?php
/**
 * Account Navigation Block
 *
 * @package Jankx\Gutenberg\Blocks
 */

namespace Jankx\Extensions\MyAccount;

class AccountNavBlock extends Block
{
    protected $blockId = 'jankx/account-nav';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $orientation = $attributes['orientation'] ?? 'vertical';

        // Get current active tab
        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
        }

        // Get registered sub-pages
        $subPages = $this->getSubPages();
        $pageId = get_option('jankx_my_account_page_id', 0);
        $accountUrl = $pageId ? get_permalink($pageId) : '#';

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-nav orientation-' . esc_attr($orientation),
        ]);

        $output = sprintf('<nav %s>', $wrapperAttrs);
        $output .= '<ul class="jankx-nav-list">';

        foreach ($subPages as $slug => $page) {
            if (empty($page['show_in_nav'])) {
                continue;
            }

            $url = rtrim($accountUrl, '/') . '/' . $slug . '/';
            $isActive = $activeTab === $slug;
            $activeClass = $isActive ? ' jankx-nav-active' : '';

            $output .= '<li class="jankx-nav-item' . $activeClass . '">';
            $output .= '<a href="' . esc_url($url) . '" class="jankx-nav-link">';

            if (!empty($page['icon'])) {
                $output .= '<span class="jankx-nav-icon">' . $page['icon'] . '</span>';
            }

            $output .= '<span class="jankx-nav-label">' . esc_html($page['label']) . '</span>';
            $output .= '</a>';
            $output .= '</li>';
        }

        $output .= '</ul>';
        $output .= '</nav>';

        return $output;
    }

    protected function getSubPages(): array
    {
        // Check if MyAccountExtension is available
        if (class_exists('\Jankx\Extensions\MyAccount\MyAccountExtension')) {
            return \Jankx\Extensions\MyAccount\MyAccountExtension::getSubPages();
        }

        // Fallback default pages
        return [
            'profile' => [
                'label' => 'Profile',
                'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                'show_in_nav' => true,
            ],
        ];
    }
}
