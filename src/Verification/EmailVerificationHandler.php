<?php
namespace Jankx\Extensions\MyAccount\Verification;

class EmailVerificationHandler
{
    const NONCE_ACTION = 'jankx_verify_email';

    private EmailVerificationService $service;

    public function __construct(?EmailVerificationService $service = null)
    {
        $this->service = $service ?? EmailVerificationService::getInstance();
    }

    public function register(): void
    {
        add_action('template_redirect', [$this, 'handleVerifyRequest']);
        add_action('wp_ajax_jankx_send_verify_email', [$this, 'ajaxSendVerification']);
        add_action('wp_ajax_nopriv_jankx_send_verify_email', [$this, 'ajaxSendVerification']);

        add_action('jankx_my_account/overview/email_verification', [$this, 'renderVerificationBanner']);

        add_filter('jankx_my_account/profile/email_field', [$this, 'addVerificationStatus'], 10, 2);
    }

    public function handleVerifyRequest(): void
    {
        if (!isset($_GET['jankx_verify']) || !isset($_GET['token'])) {
            return;
        }

        $userId = intval($_GET['jankx_verify']);
        $token = sanitize_text_field($_GET['token']);

        if (!$userId || empty($token)) {
            $this->redirectWithMessage('error', 'Liên kết xác nhận không hợp lệ.');
            return;
        }

        $success = $this->service->verifyEmail($userId, $token);

        if ($success) {
            $this->redirectWithMessage('success', 'Email đã được xác nhận thành công!');
        } else {
            $this->redirectWithMessage('error', 'Liên kết xác nhận đã hết hạn hoặc không hợp lệ. Vui lòng yêu cầu gửi lại.');
        }
    }

    public function ajaxSendVerification(): void
    {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Vui lòng đăng nhập.']);
        }

        $userId = get_current_user_id();

        if ($this->service->isVerified($userId)) {
            wp_send_json_error(['message' => 'Email đã được xác nhận rồi.']);
        }

        $sent = $this->service->sendVerificationEmail($userId);

        if ($sent) {
            wp_send_json_success([
                'message' => 'Email xác nhận đã được gửi. Vui lòng kiểm tra hộp thư của bạn.',
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Không thể gửi email xác nhận. Vui lòng thử lại sau.',
            ]);
        }
    }

    public function renderVerificationBanner(): void
    {
        if (!is_user_logged_in()) {
            return;
        }

        $userId = get_current_user_id();
        if ($this->service->isVerified($userId)) {
            return;
        }

        $nonce = wp_create_nonce(self::NONCE_ACTION);

        echo '<div class="jankx-email-verify-banner" data-nonce="' . esc_attr($nonce) . '">';
        echo '<div class="jankx-verify-icon">';
        echo '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
        echo '</div>';
        echo '<div class="jankx-verify-content">';
        echo '<strong>Email chưa xác nhận</strong>';
        echo '<p>Vui lòng xác nhận email để sử dụng đầy đủ tính năng.</p>';
        echo '</div>';
        echo '<button type="button" class="jankx-btn jankx-btn-sm jankx-btn-verify-send">Gửi email xác nhận</button>';
        echo '</div>';
    }

    public function addVerificationStatus(string $emailHtml, WP_User $user): string
    {
        $isVerified = $this->service->isVerified($user->ID);

        if ($isVerified) {
            $statusHtml = '<span class="jankx-email-verified">';
            $statusHtml .= '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
            $statusHtml .= ' Đã xác nhận</span>';
        } else {
            $nonce = wp_create_nonce(self::NONCE_ACTION);
            $statusHtml = '<span class="jankx-email-unverified">';
            $statusHtml .= 'Email chưa xác nhận ';
            $statusHtml .= '<a href="#" class="jankx-verify-email-link" data-nonce="' . esc_attr($nonce) . '">Click để xác nhận</a>';
            $statusHtml .= '</span>';
        }

        return $emailHtml . $statusHtml;
    }

    private function redirectWithMessage(string $type, string $message): void
    {
        $pageId = get_option('jankx_my_account_page_id', 0);
        $redirectUrl = $pageId ? get_permalink($pageId) : home_url('/');

        $redirectUrl = add_query_arg([
            'tab' => 'profile',
            'verify_status' => $type,
            'verify_msg' => urlencode($message),
        ], $redirectUrl);

        wp_safe_redirect($redirectUrl);
        exit;
    }
}
