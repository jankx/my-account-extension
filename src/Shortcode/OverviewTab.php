<?php
namespace Jankx\Extensions\MyAccount\Shortcode;

class OverviewTab
{
    private static $rendering = false;

    public function render($user): void
    {
        if (self::$rendering) {
            return;
        }
        self::$rendering = true;

        ?>
        <div class="jankx-tab-panel jankx-tab-overview">
            <?php
            /**
             * Fires at the top of the My Account overview page.
             * Use for welcome messages, banners, announcements, etc.
             *
             * @param WP_User $user Current user object.
             */
            do_action('jankx/my_account/overview/top', $user);
            ?>

            <?php
            /**
             * Fires to render the membership tier section.
             * Default output shows current tier + progress bar.
             *
             * @param WP_User $user Current user object.
             */
            do_action('jankx/my_account/overview/membership', $user);
            ?>

            <?php
            /**
             * Fires to render quick links section.
             * Common links: bookings, wishlist, support, etc.
             *
             * @param WP_User $user Current user object.
             */
            do_action('jankx/my_account/overview/quick_links', $user);
            ?>

            <?php
            /**
             * Fires to render the main dashboard content.
             * Upcoming tours, recent activity, recommendations, etc.
             *
             * @param WP_User $user Current user object.
             */
            do_action('jankx/my_account/overview/main', $user);
            ?>

            <?php
            /**
             * Fires to render the Q&A / help section.
             * FAQ about memberships, accounts, bookings, etc.
             *
             * @param WP_User $user Current user object.
             */
            do_action('jankx/my_account/overview/qa', $user);
            ?>

            <?php
            /**
             * Fires at the bottom of the overview page.
             * Use for promotional content, newsletters, etc.
             *
             * @param WP_User $user Current user object.
             */
            do_action('jankx/my_account/overview/bottom', $user);
            ?>
        </div>
        <?php

        self::$rendering = false;
    }

    /**
     * Default membership section renderer.
     * Can be removed via remove_action('jankx/my_account/overview/membership', ...).
     */
    public static function renderMembership($user): void
    {
        $level = get_user_meta($user->ID, 'jankx_user_level', true) ?: 'bronze';

        $levels = [
            'bronze' => [
                'name' => 'Bronze',
                'points' => 0,
                'next' => 'Silver',
                'next_points' => 500,
                'color' => '#CD7F32',
            ],
            'silver' => [
                'name' => 'Silver',
                'points' => 500,
                'next' => 'Gold',
                'next_points' => 2000,
                'color' => '#65A30D',
            ],
            'gold' => [
                'name' => 'Gold',
                'points' => 2000,
                'next' => null,
                'next_points' => null,
                'color' => '#F59E0B',
            ],
        ];

        $current = $levels[$level] ?? $levels['bronze'];
        $points = (int) get_user_meta($user->ID, 'jankx_points', true);
        $progress = 0;

        if ($current['next'] && $current['next_points'] > $current['points']) {
            $progress = min(100, ($points - $current['points']) / ($current['next_points'] - $current['points']) * 100);
        }
        ?>
        <div class="jankx-overview-section jankx-overview-membership">
            <h3 class="jankx-overview-section-title">
                <?php _e('Membership Tier', 'jankx'); ?>
            </h3>
            <div class="jankx-membership-card" style="--tier-color: <?php echo esc_attr($current['color']); ?>">
                <div class="jankx-membership-tier">
                    <span class="jankx-tier-badge"><?php echo esc_html($current['name']); ?></span>
                    <?php if ($current['next']): ?>
                        <span class="jankx-tier-next">
                            <?php printf(__('Next: %s', 'jankx'), esc_html($current['next'])); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="jankx-membership-points">
                    <span class="jankx-points-value"><?php echo number_format($points); ?></span>
                    <span class="jankx-points-label"><?php _e('points', 'jankx'); ?></span>
                </div>
                <?php if ($current['next']): ?>
                    <div class="jankx-progress-bar">
                        <div class="jankx-progress-fill" style="width: <?php echo esc_attr($progress); ?>%"></div>
                    </div>
                    <div class="jankx-progress-text">
                        <?php
                        printf(
                            __('%1$d / %2$d points to %3$s', 'jankx'),
                            $points,
                            $current['next_points'],
                            esc_html($current['next'])
                        );
                        ?>
                    </div>
                <?php else: ?>
                    <div class="jankx-tier-maxed"><?php _e('You are at the highest tier!', 'jankx'); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Default quick links section renderer.
     */
    public static function renderQuickLinks($user): void
    {
        $pageId = get_option('jankx_my_account_page_id', 0);
        $baseUrl = $pageId ? get_permalink($pageId) : '#';

        $links = [
            [
                'label' => __('My Bookings', 'jankx'),
                'url'   => rtrim($baseUrl, '/') . '/orders/',
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
            ],
            [
                'label' => __('My Profile', 'jankx'),
                'url'   => rtrim($baseUrl, '/') . '/profile/',
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            ],
            [
                'label' => __('Settings', 'jankx'),
                'url'   => rtrim($baseUrl, '/') . '/settings/',
                'icon'  => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
            ],
        ];

        /**
         * Filter the quick links shown on the overview page.
         *
         * @param array    $links Array of ['label', 'url', 'icon'] items.
         * @param WP_User  $user  Current user object.
         */
        $links = apply_filters('jankx/my_account/overview/quick_links/links', $links, $user);
        ?>
        <div class="jankx-overview-section jankx-overview-quick-links">
            <h3 class="jankx-overview-section-title">
                <?php _e('Quick Links', 'jankx'); ?>
            </h3>
            <div class="jankx-quick-links-grid">
                <?php foreach ($links as $link): ?>
                    <a href="<?php echo esc_url($link['url']); ?>" class="jankx-quick-link-card">
                        <span class="jankx-quick-link-icon"><?php echo $link['icon']; ?></span>
                        <span class="jankx-quick-link-label"><?php echo esc_html($link['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Default Q&A section renderer.
     *
     * The FAQ items come from the `jankx/my_account/overview/qa/faqs` filter
     * (e.g. supplied dynamically by the FAQ extension) instead of being
     * hardcoded. The section is skipped when no FAQ is provided.
     */
    public static function renderQA($user): void
    {
        /**
         * Filter the Q&A items on the overview page.
         *
         * @param array   $faqs Array of ['question', 'answer'] items.
         * @param WP_User $user Current user object.
         */
        $faqs = apply_filters('jankx/my_account/overview/qa/faqs', [], $user);

        if (empty($faqs)) {
            return;
        }

        /**
         * Filter the Q&A section title on the overview page.
         *
         * @param string  $title Section title.
         * @param WP_User $user  Current user object.
         */
        $title = apply_filters('jankx/my_account/overview/qa/title', __('Frequently Asked Questions', 'jankx'), $user);
        ?>
        <div class="jankx-overview-section jankx-overview-qa">
            <h3 class="jankx-overview-section-title">
                <?php echo esc_html($title); ?>
            </h3>
            <div class="jankx-qa-list">
                <?php foreach ($faqs as $faq): ?>
                    <details class="jankx-qa-item">
                        <summary class="jankx-qa-question"><?php echo esc_html($faq['question']); ?></summary>
                        <div class="jankx-qa-answer">
                            <p><?php echo wp_kses_post($faq['answer']); ?></p>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
