<?php
/**
 * Account Tab Profile Block
 *
 * @package Jankx\Gutenberg\Blocks
 */

namespace Jankx\Extensions\MyAccount;

class AccountTabProfileBlock extends Block
{
    protected $blockId = 'jankx/account-tab-profile';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        // Only render if this is the active tab
        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
        }

        $is_editor = defined('REST_REQUEST') && REST_REQUEST && !empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/block-renderer/') !== false;

        if (!$is_editor && $activeTab !== 'profile') {
            return '';
        }

        $user = wp_get_current_user();
        $phone = get_user_meta($user->ID, 'phone', true);

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-content jankx-tab-panel jankx-tab-profile',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);

        // Profile form
        $output .= '<h2 class="jankx-section-title">Personal Information</h2>';
        $output .= '<form id="jankx-profile-form" class="jankx-form">';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-display-name">Full Name</label>';
        $output .= sprintf(
            '<input type="text" id="jankx-display-name" name="display_name" value="%s" required>',
            esc_attr($user->display_name)
        );
        $output .= '</div>';

        $output .= '<div class="jankx-form-row">';
        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-email">Email</label>';
        $output .= sprintf(
            '<input type="email" id="jankx-email" name="email" value="%s" required>',
            esc_attr($user->user_email)
        );
        $output .= '</div>';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-phone">Phone Number</label>';
        $output .= sprintf(
            '<input type="tel" id="jankx-phone" name="phone" value="%s" placeholder="e.g. 0912345678">',
            esc_attr($phone)
        );
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="jankx-form-actions">';
        $output .= '<button type="submit" class="jankx-btn jankx-btn-primary" id="jankx-save-profile">Save Changes</button>';
        $output .= '<span class="jankx-form-status" id="jankx-profile-status"></span>';
        $output .= '</div>';
        $output .= '</form>';

        $output .= '<div class="jankx-divider"></div>';

        // Password form
        $output .= '<h2 class="jankx-section-title">Change Password</h2>';
        $output .= '<form id="jankx-password-form" class="jankx-form">';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-current-password">Current Password</label>';
        $output .= '<input type="password" id="jankx-current-password" name="current_password" required>';
        $output .= '</div>';

        $output .= '<div class="jankx-form-row">';
        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-new-password">New Password</label>';
        $output .= '<input type="password" id="jankx-new-password" name="new_password" minlength="8" required>';
        $output .= '</div>';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-confirm-password">Confirm New Password</label>';
        $output .= '<input type="password" id="jankx-confirm-password" name="confirm_password" minlength="8" required>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="jankx-form-actions">';
        $output .= '<button type="submit" class="jankx-btn jankx-btn-primary" id="jankx-change-password">Change Password</button>';
        $output .= '<span class="jankx-form-status" id="jankx-password-status"></span>';
        $output .= '</div>';
        $output .= '</form>';

        $output .= '</div>';

        // Enqueue scripts
        $this->enqueueAssets();

        return $output;
    }

    protected function enqueueAssets(): void
    {
        wp_enqueue_style(
            'jankx-my-account',
            get_stylesheet_directory_uri() . '/extensions/my-account/assets/frontend.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'jankx-my-account',
            get_stylesheet_directory_uri() . '/extensions/my-account/assets/frontend.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('jankx-my-account', 'jankxMyAccount', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jankx_my_account_nonce'),
        ]);
    }
}
