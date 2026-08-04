<?php
namespace Jankx\Extensions\MyAccount;

class SidebarNavBlock extends Block
{
    protected $blockId = 'jankx/sidebar-nav';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
        }

        $subPages = $this->getSubPages();
        $pageId = get_option('jankx_my_account_page_id', 0);
        $accountUrl = $pageId ? get_permalink($pageId) : '#';

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-sidebar-nav',
        ]);

        $output = sprintf('<nav %s>', $wrapperAttrs);
        $output .= '<ul class="jankx-nav-list">';

        foreach ($subPages as $slug => $page) {
            if (empty($page['show_in_nav'])) continue;

            $url = rtrim($accountUrl, '/') . '/' . $slug . '/';
            $isActive = $activeTab === $slug;
            $activeClass = $isActive ? ' jankx-nav-active' : '';

            $output .= '<li class="jankx-nav-item' . $activeClass . '">';
            $output .= '<a href="' . esc_url($url) . '" class="jankx-nav-link">';
            if (!empty($page['icon'])) {
                $output .= '<span class="jankx-nav-icon">' . $page['icon'] . '</span>';
            }
            $output .= '<span class="jankx-nav-label">' . esc_html($page['label']) . '</span>';
            $output .= '</a></li>';
        }

        $output .= '</ul></nav>';
        return $output;
    }

    protected function getSubPages(): array
    {
        if (class_exists('\Jankx\Extensions\MyAccount\MyAccountExtension')) {
            return \Jankx\Extensions\MyAccount\MyAccountExtension::getSubPages();
        }
        return [];
    }
}
