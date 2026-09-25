<?php
namespace Jankx\Extensions\MyAccount;

class SidebarHeaderBlock extends Block
{
    protected $blockId = 'jankx/sidebar-header';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user = wp_get_current_user();
        $showAvatar = $attributes['showAvatar'] ?? true;
        $showName = $attributes['showName'] ?? true;
        $showEditLink = $attributes['showEditLink'] ?? true;

        $overlayColor = isset($attributes['overlayColor']) && is_string($attributes['overlayColor'])
            ? sanitize_hex_color($attributes['overlayColor'])
            : '';
        $overlayOpacity = isset($attributes['overlayOpacity']) && is_numeric($attributes['overlayOpacity'])
            ? max(0, min(100, (int) $attributes['overlayOpacity']))
            : 50;
        $hasOverlay = $overlayColor !== '';

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-sidebar-header' . ($hasOverlay ? ' has-overlay' : ''),
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);

        if ($hasOverlay) {
            $output .= sprintf(
                '<span class="jankx-sidebar-header__overlay" style="background:%s;opacity:%s;" aria-hidden="true"></span>',
                esc_attr($overlayColor),
                esc_attr($overlayOpacity / 100)
            );
        }

        $output .= '<div class="jankx-sidebar-header__content">';

        if ($showAvatar) {
            $avatarId = get_user_meta($user->ID, 'jankx_avatar_id', true);
            $avatarUrl = $avatarId ? wp_get_attachment_image_url($avatarId, 'medium') : get_avatar_url($user->ID, ['size' => 120]);
            $output .= '<div class="jankx-avatar-wrapper">';
            $output .= sprintf('<img src="%s" alt="%s" class="jankx-avatar-img">', esc_url($avatarUrl), esc_attr($user->display_name));
            $output .= '</div>';
        }

        if ($showName) {
            $output .= sprintf('<h2 class="jankx-user-name">%s</h2>', esc_html($user->display_name));
        }

        if ($showEditLink) {
            $pageId = get_option('jankx_my_account_page_id', 0);
            $profileUrl = $pageId ? add_query_arg('tab', 'profile', get_permalink($pageId)) : '#';
            $output .= sprintf(
                '<a href="%s" class="jankx-edit-profile-link">Edit Profile <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a>',
                esc_url($profileUrl)
            );
        }

        $output .= '</div></div>';

        return $output;
    }
}
