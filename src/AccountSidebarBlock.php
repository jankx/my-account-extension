<?php
/**
 * Account Sidebar Block
 *
 * @package Jankx\Gutenberg\Blocks
 */

namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;

class AccountSidebarBlock extends Block
{
    protected $blockId = 'jankx/account-sidebar';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user = wp_get_current_user();
        $showAvatar = $attributes['showAvatar'] ?? true;
        $showName = $attributes['showName'] ?? true;
        $showMembershipBadge = $attributes['showMembershipBadge'] ?? true;
        $showEditLink = $attributes['showEditLink'] ?? true;

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-sidebar',
        ]);

        $output = sprintf('<aside %s>', $wrapperAttrs);

        // Sidebar header
        $output .= '<div class="jankx-sidebar-header">';

        if ($showAvatar) {
            $avatarId = get_user_meta($user->ID, 'jankx_avatar_id', true);
            $avatarUrl = $avatarId ? wp_get_attachment_image_url($avatarId, 'medium') : get_avatar_url($user->ID, ['size' => 120]);

            $output .= '<div class="jankx-avatar-wrapper">';
            $output .= sprintf(
                '<img src="%s" alt="%s" class="jankx-avatar-img">',
                esc_url($avatarUrl),
                esc_attr($user->display_name)
            );
            $output .= '</div>';
        }

        if ($showName) {
            $output .= sprintf(
                '<h2 class="jankx-user-name">%s</h2>',
                esc_html($user->display_name)
            );
        }

        if ($showEditLink) {
            $pageId = get_option('jankx_my_account_page_id', 0);
            $profileUrl = $pageId ? add_query_arg('tab', 'profile', get_permalink($pageId)) : '#';
            $output .= sprintf(
                '<a href="%s" class="jankx-edit-profile-link">Edit Profile <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a>',
                esc_url($profileUrl)
            );
        }

        $output .= '</div>';

        // Membership badge
        if ($showMembershipBadge) {
            $output .= $this->renderMembershipBadge($user);
        }

        // Inner blocks (for navigation)
        $output .= $content;

        $output .= '</aside>';

        return $output;
    }

    protected function renderMembershipBadge($user): string
    {
        $levelSlug = get_user_meta($user->ID, 'jankx_membership_level', true) ?: 'bronze';

        $levels = [
            'bronze' => [
                'name' => 'Bronze',
                'description' => 'New member. Accumulate points to upgrade.',
                'color' => '#CD7F32',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
            ],
            'silver' => [
                'name' => 'Silver',
                'description' => 'Exclusive deals and offers just for you.',
                'color' => '#94A3B8',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
            ],
            'gold' => [
                'name' => 'Gold',
                'description' => 'Premium benefits and VIP service.',
                'color' => '#F59E0B',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
            ],
        ];

        $level = $levels[$levelSlug] ?? $levels['bronze'];

        $output = '<div class="jankx-membership-badge" style="--badge-color: ' . esc_attr($level['color']) . '; background: linear-gradient(135deg, ' . esc_attr($level['color']) . '15, ' . esc_attr($level['color']) . '05); border: 1px solid ' . esc_attr($level['color']) . '30; border-radius: 12px; padding: 16px; margin-bottom: 16px;">';
        $output .= '<div style="display: flex; align-items: center; gap: 12px;">';
        $output .= '<div style="width: 48px; height: 48px; border-radius: 50%; background: ' . esc_attr($level['color']) . '20; display: flex; align-items: center; justify-content: center; color: ' . esc_attr($level['color']) . ';">';
        $output .= $level['icon'];
        $output .= '</div>';
        $output .= '<div style="flex: 1;">';
        $output .= '<h3 style="margin: 0; font-size: 16px; font-weight: 700; color: ' . esc_attr($level['color']) . ';">' . esc_html($level['name']) . '</h3>';
        $output .= '<p style="margin: 4px 0 0; font-size: 13px; color: #666; line-height: 1.4;">' . esc_html($level['description']) . '</p>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }
}
