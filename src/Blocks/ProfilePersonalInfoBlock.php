<?php

namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;

class ProfilePersonalInfoBlock extends Block
{
    protected $blockId = 'jankx/profile-personal-info';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user = wp_get_current_user();
        $phone = get_user_meta($user->ID, 'phone', true);

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-profile-section jankx-profile-personal-info',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);

        $output .= '<h2 class="jankx-section-title">' . esc_html__('Thông tin cá nhân', 'jankx') . '</h2>';
        $output .= '<form id="jankx-profile-form" class="jankx-form">';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-display-name">' . esc_html__('Họ và tên', 'jankx') . '</label>';
        $output .= sprintf(
            '<input type="text" id="jankx-display-name" name="display_name" value="%s" required>',
            esc_attr($user->display_name)
        );
        $output .= '</div>';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-email">' . esc_html__('Email', 'jankx') . '</label>';
        $output .= sprintf(
            '<input type="email" id="jankx-email" name="email" value="%s" required>',
            esc_attr($user->user_email)
        );
        $output .= $this->renderEmailVerificationStatus($user->ID);
        $output .= '</div>';

        $output .= '<div class="jankx-form-group">';
        $output .= '<label for="jankx-phone">' . esc_html__('Số điện thoại', 'jankx') . '</label>';
        $output .= sprintf(
            '<input type="tel" id="jankx-phone" name="phone" value="%s" placeholder="VD: 0912345678">',
            esc_attr($phone)
        );
        $output .= '</div>';

        $output .= '<div class="jankx-form-actions">';
        $output .= '<button type="submit" class="jankx-btn jankx-btn-primary" id="jankx-save-profile">' . esc_html__('Lưu thay đổi', 'jankx') . '</button>';
        $output .= '<span class="jankx-form-status" id="jankx-profile-status"></span>';
        $output .= '</div>';
        $output .= '</form>';

        $output .= '</div>';

        $this->enqueueAssets();

        return $output;
    }

    protected function renderEmailVerificationStatus(int $userId): string
    {
        $verificationService = \Jankx\Extensions\MyAccount\Verification\EmailVerificationService::getInstance();
        $isVerified = $verificationService->isVerified($userId);

        if ($isVerified) {
            return '<span class="jankx-email-verified">'
                . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
                . ' ' . esc_html__('Đã xác nhận', 'jankx') . '</span>';
        }

        $nonce = wp_create_nonce(\Jankx\Extensions\MyAccount\Verification\EmailVerificationHandler::NONCE_ACTION);

        return '<span class="jankx-email-unverified">'
            . esc_html__('Email chưa xác nhận', 'jankx') . ' '
            . '<a href="#" class="jankx-verify-email-link" data-nonce="' . esc_attr($nonce) . '">' . esc_html__('Click để xác nhận', 'jankx') . '</a>'
            . '</span>';
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