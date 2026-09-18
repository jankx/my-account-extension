<?php

namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;

class AccountTabOverviewBlock extends Block
{
    protected $blockId = 'jankx/account-tab-overview';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'overview';
        }

        $is_editor = defined('REST_REQUEST') && REST_REQUEST
            && !empty($_SERVER['REQUEST_URI'])
            && strpos($_SERVER['REQUEST_URI'], '/block-renderer/') !== false;

        if (!$is_editor && $activeTab !== 'overview') {
            return '';
        }

        $user = wp_get_current_user();
        $pageId = get_option('jankx_my_account_page_id', 0);
        $baseUrl = $pageId ? get_permalink($pageId) : '#';

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-tab-panel jankx-tab-overview',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);

        // ── Top section ──
        $output .= '<div class="jankx-overview-section jankx-overview-top">';
        ob_start();
        do_action('jankx/my_account/overview/top', $user);
        $output .= ob_get_clean();
        $output .= '</div>';

        // ── Membership section ──
        $output .= '<div class="jankx-overview-section jankx-overview-membership">';
        $output .= '<h3 class="jankx-overview-section-title">' . esc_html__('Hạng thành viên', 'jankx') . '</h3>';
        $output .= $this->renderMembership($user);
        ob_start();
        do_action('jankx/my_account/overview/membership', $user);
        $output .= ob_get_clean();
        $output .= '</div>';

        // ── Quick links section ──
        $output .= '<div class="jankx-overview-section jankx-overview-quick-links">';
        $output .= '<h3 class="jankx-overview-section-title">' . esc_html__('Liên kết nhanh', 'jankx') . '</h3>';
        $output .= $this->renderQuickLinks($user, $baseUrl);
        ob_start();
        do_action('jankx/my_account/overview/quick_links', $user);
        $output .= ob_get_clean();
        $output .= '</div>';

        // ── Main content section ──
        $output .= '<div class="jankx-overview-section jankx-overview-main">';
        ob_start();
        do_action('jankx/my_account/overview/main', $user);
        $output .= ob_get_clean();
        $output .= '</div>';

        // ── Q&A section ──
        $qaContent = $this->renderQA($user);
        if ($qaContent !== '') {
            $output .= '<div class="jankx-overview-section jankx-overview-qa">';
            $output .= $qaContent;
            ob_start();
            do_action('jankx/my_account/overview/qa', $user);
            $output .= ob_get_clean();
            $output .= '</div>';
        }

        // ── Bottom section ──
        $output .= '<div class="jankx-overview-section jankx-overview-bottom">';
        ob_start();
        do_action('jankx/my_account/overview/bottom', $user);
        $output .= ob_get_clean();
        $output .= '</div>';

        $output .= '</div>';

        return $output;
    }

    protected function renderMembership($user): string
    {
        $level = get_user_meta($user->ID, 'jankx_user_level', true) ?: 'bronze';
        $points = (int) get_user_meta($user->ID, 'jankx_points', true);

        $levels = [
            'bronze' => [
                'name' => 'Bronze', 'points' => 0,
                'next' => 'Silver', 'next_points' => 500, 'color' => '#CD7F32',
            ],
            'silver' => [
                'name' => 'Silver', 'points' => 500,
                'next' => 'Gold', 'next_points' => 2000, 'color' => '#65A30D',
            ],
            'gold' => [
                'name' => 'Gold', 'points' => 2000,
                'next' => null, 'next_points' => null, 'color' => '#F59E0B',
            ],
        ];

        $current = $levels[$level] ?? $levels['bronze'];
        $progress = 0;
        if ($current['next'] && $current['next_points'] > $current['points']) {
            $progress = min(100, ($points - $current['points']) / ($current['next_points'] - $current['points']) * 100);
        }

        $output = '<div class="jankx-membership-card" style="--tier-color: ' . esc_attr($current['color']) . '">';
        $output .= '<div class="jankx-membership-tier">';
        $output .= '<span class="jankx-tier-badge">' . esc_html($current['name']) . '</span>';
        if ($current['next']) {
            $output .= '<span class="jankx-tier-next">' . sprintf(__('Tiếp theo: %s', 'jankx'), esc_html($current['next'])) . '</span>';
        }
        $output .= '</div>';
        $output .= '<div class="jankx-membership-points">';
        $output .= '<span class="jankx-points-value">' . number_format($points) . '</span>';
        $output .= '<span class="jankx-points-label">' . esc_html__('điểm', 'jankx') . '</span>';
        $output .= '</div>';
        if ($current['next']) {
            $output .= '<div class="jankx-progress-bar"><div class="jankx-progress-fill" style="width: ' . esc_attr($progress) . '%"></div></div>';
            $output .= '<div class="jankx-progress-text">' . sprintf(__('%1$d / %2$d điểm để lên %3$s', 'jankx'), $points, $current['next_points'], esc_html($current['next'])) . '</div>';
        } else {
            $output .= '<div class="jankx-tier-maxed">' . esc_html__('Bạn đang ở hạng cao nhất!', 'jankx') . '</div>';
        }
        $output .= '</div>';

        return $output;
    }

    protected function renderQuickLinks($user, $baseUrl): string
    {
        $links = [
            [
                'label' => __('Đơn đặt tour', 'jankx'),
                'url'   => rtrim($baseUrl, '/') . '/orders/',
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
            ],
            [
                'label' => __('Hồ sơ', 'jankx'),
                'url'   => rtrim($baseUrl, '/') . '/profile/',
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            ],
            [
                'label' => __('Mã giảm giá', 'jankx'),
                'url'   => rtrim($baseUrl, '/') . '/coupons/',
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>',
            ],
        ];

        /**
         * Filter the quick links shown on the overview tab.
         *
         * @param array   $links Array of ['label', 'url', 'icon'] items.
         * @param WP_User $user  Current user object.
         */
        $links = apply_filters('jankx/my_account/overview/quick_links/links', $links, $user);

        $output = '<div class="jankx-quick-links-grid">';
        foreach ($links as $link) {
            $output .= '<a href="' . esc_url($link['url']) . '" class="jankx-quick-link-card">';
            $output .= '<span class="jankx-quick-link-icon">' . ($link['icon'] ?? '') . '</span>';
            $output .= '<span class="jankx-quick-link-label">' . esc_html($link['label']) . '</span>';
            $output .= '</a>';
        }
        $output .= '</div>';

        return $output;
    }

    protected function renderQA($user): string
    {
        $faqs = apply_filters('jankx/my_account/overview/qa/faqs', [], $user);
        if (empty($faqs)) {
            return '';
        }

        $title = apply_filters('jankx/my_account/overview/qa/title', __('Câu hỏi thường gặp', 'jankx'), $user);

        $output = '<h3 class="jankx-overview-section-title">' . esc_html($title) . '</h3>';
        $output .= '<div class="jankx-qa-list">';
        foreach ($faqs as $faq) {
            $output .= '<details class="jankx-qa-item">';
            $output .= '<summary class="jankx-qa-question">' . esc_html($faq['question']) . '</summary>';
            $output .= '<div class="jankx-qa-answer"><p>' . wp_kses_post($faq['answer']) . '</p></div>';
            $output .= '</details>';
        }
        $output .= '</div>';

        return $output;
    }
}