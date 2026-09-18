<?php

namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;

class ProfileChangePasswordBlock extends Block
{
    protected $blockId = 'jankx/profile-change-password';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-profile-section jankx-profile-change-password',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);

        $output .= '<h2 class="jankx-section-title">' . esc_html__('Đổi mật khẩu', 'jankx') . '</h2>';
        $output .= '<form id="jankx-password-form" class="jankx-form">';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-current-password">' . esc_html__('Mật khẩu hiện tại', 'jankx') . '</label>';
        $output .= '<input type="password" id="jankx-current-password" name="current_password" required>';
        $output .= '</div>';

        $output .= '<div class="jankx-form-row">';
        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-new-password">' . esc_html__('Mật khẩu mới', 'jankx') . '</label>';
        $output .= '<input type="password" id="jankx-new-password" name="new_password" minlength="8" required>';
        $output .= '</div>';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-confirm-password">' . esc_html__('Xác nhận mật khẩu mới', 'jankx') . '</label>';
        $output .= '<input type="password" id="jankx-confirm-password" name="confirm_password" minlength="8" required>';
        $output .= '</div>';
        $output .= '</div>';

        $output .= '<div class="jankx-form-actions">';
        $output .= '<button type="submit" class="jankx-btn jankx-btn-primary" id="jankx-change-password">' . esc_html__('Đổi mật khẩu', 'jankx') . '</button>';
        $output .= '<span class="jankx-form-status" id="jankx-password-status"></span>';
        $output .= '</div>';
        $output .= '</form>';

        $output .= '</div>';

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
            'i18n' => [
                'saving' => 'Đang lưu...',
                'saved' => 'Đã lưu thành công!',
                'error' => 'Có lỗi xảy ra. Vui lòng thử lại.',
                'uploading' => 'Đang tải lên...',
            ],
        ]);
    }
}