<?php
namespace Jankx\Extensions\MyAccount\Handler;

class ProfileHandler
{
    const NONCE_ACTION = 'jankx_profile_nonce';
    const AJAX_NONCE_ACTION = 'jankx_my_account_nonce';

    public function register(): void
    {
        add_action('init', [$this, 'handleFormSubmission']);
        add_action('wp_ajax_jankx_update_profile', [$this, 'ajaxUpdateProfile']);
        add_action('wp_ajax_jankx_upload_avatar', [$this, 'ajaxUploadAvatar']);
        add_action('wp_ajax_jankx_change_password', [$this, 'ajaxChangePassword']);
        add_action('wp_ajax_jankx_save_settings', [$this, 'ajaxSaveSettings']);
    }

    public function handleFormSubmission(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['jankx_profile_nonce']) ||
            !wp_verify_nonce($_POST['jankx_profile_nonce'], self::NONCE_ACTION)) {
            return;
        }

        $action = $_POST['jankx_action'] ?? '';

        switch ($action) {
            case 'update_profile':
                $this->processProfileUpdate();
                break;
            case 'change_password':
                $this->processPasswordChange();
                break;
        }
    }

    protected function processProfileUpdate(): void
    {
        if (!is_user_logged_in()) {
            wp_die('Unauthorized');
        }

        $userId = get_current_user_id();
        if (!current_user_can('edit_user', $userId)) {
            wp_die('Forbidden');
        }

        $displayName = sanitize_text_field($_POST['display_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');

        $errors = [];

        if (empty($displayName)) {
            $errors[] = 'Họ tên không được để trống.';
        }

        if (empty($email) || !is_email($email)) {
            $errors[] = 'Email không hợp lệ.';
        }

        if (empty($errors)) {
            $existingUser = email_exists($email);
            if ($existingUser && $existingUser !== $userId) {
                $errors[] = 'Email đã được sử dụng bởi tài khoản khác.';
            }
        }

        if (!empty($errors)) {
            return;
        }

        wp_update_user([
            'ID' => $userId,
            'display_name' => $displayName,
            'user_email' => $email,
        ]);

        update_user_meta($userId, 'phone', $phone);

        wp_safe_redirect(add_query_arg('tab', 'profile', get_permalink()));
        exit;
    }

    protected function processPasswordChange(): void
    {
        if (!is_user_logged_in()) {
            wp_die('Unauthorized');
        }

        $userId = get_current_user_id();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            return;
        }

        $user = get_userdata($userId);
        if (!wp_check_password($currentPassword, $user->user_pass, $userId)) {
            return;
        }

        if (strlen($newPassword) < 8) {
            return;
        }

        if ($newPassword !== $confirmPassword) {
            return;
        }

        wp_set_password($newPassword, $userId);

        wp_safe_redirect(add_query_arg('tab', 'profile', get_permalink()));
        exit;
    }

    public function ajaxUpdateProfile(): void
    {
        check_ajax_referer(self::AJAX_NONCE_ACTION, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Vui lòng đăng nhập.']);
        }

        $userId = get_current_user_id();
        if (!current_user_can('edit_user', $userId)) {
            wp_send_json_error(['message' => 'Bạn không có quyền thực hiện thao tác này.']);
        }

        $displayName = sanitize_text_field($_POST['display_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');

        if (empty($displayName)) {
            wp_send_json_error(['message' => 'Họ tên không được để trống.']);
        }

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => 'Email không hợp lệ.']);
        }

        $existingUser = email_exists($email);
        if ($existingUser && $existingUser !== $userId) {
            wp_send_json_error(['message' => 'Email đã được sử dụng bởi tài khoản khác.']);
        }

        $result = wp_update_user([
            'ID' => $userId,
            'display_name' => $displayName,
            'user_email' => $email,
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => 'Có lỗi xảy ra khi cập nhật hồ sơ.']);
        }

        update_user_meta($userId, 'phone', $phone);

        wp_send_json_success([
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => [
                'display_name' => $displayName,
                'email' => $email,
                'phone' => $phone,
            ],
        ]);
    }

    public function ajaxUploadAvatar(): void
    {
        check_ajax_referer(self::AJAX_NONCE_ACTION, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Vui lòng đăng nhập.']);
        }

        $userId = get_current_user_id();
        if (!current_user_can('edit_user', $userId)) {
            wp_send_json_error(['message' => 'Bạn không có quyền thực hiện thao tác này.']);
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => 'Vui lòng chọn file ảnh.']);
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowedTypes)) {
            wp_send_json_error(['message' => 'Chỉ chấp nhận file JPG, PNG, GIF hoặc WebP.']);
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(['message' => 'Kích thước file không được vượt quá 5MB.']);
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachId = media_handle_upload('avatar', 0);

        if (is_wp_error($attachId)) {
            wp_send_json_error(['message' => 'Tải ảnh lên thất bại.']);
        }

        update_user_meta($userId, 'jankx_avatar_id', $attachId);
        update_user_meta($userId, 'jankx_avatar_url', wp_get_attachment_url($attachId));

        wp_send_json_success([
            'message' => 'Cập nhật ảnh đại diện thành công!',
            'url' => wp_get_attachment_image_url($attachId, 'thumbnail'),
        ]);
    }

    public function ajaxChangePassword(): void
    {
        check_ajax_referer(self::AJAX_NONCE_ACTION, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Vui lòng đăng nhập.']);
        }

        $userId = get_current_user_id();

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) {
            wp_send_json_error(['message' => 'Vui lòng nhập mật khẩu hiện tại.']);
        }

        $user = get_userdata($userId);
        if (!wp_check_password($currentPassword, $user->user_pass, $userId)) {
            wp_send_json_error(['message' => 'Mật khẩu hiện tại không đúng.']);
        }

        if (strlen($newPassword) < 8) {
            wp_send_json_error(['message' => 'Mật khẩu mới phải có ít nhất 8 ký tự.']);
        }

        if ($newPassword !== $confirmPassword) {
            wp_send_json_error(['message' => 'Mật khẩu xác nhận không khớp.']);
        }

        wp_set_password($newPassword, $userId);

        wp_send_json_success(['message' => 'Đổi mật khẩu thành công!']);
    }

    public function ajaxSaveSettings(): void
    {
        check_ajax_referer(self::AJAX_NONCE_ACTION, 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Vui lòng đăng nhập.']);
        }

        $userId = get_current_user_id();

        $settings = [
            'email_booking' => isset($_POST['email_booking']) ? 1 : 0,
            'email_promotions' => isset($_POST['email_promotions']) ? 1 : 0,
            'email_newsletter' => isset($_POST['email_newsletter']) ? 1 : 0,
        ];

        update_user_meta($userId, 'jankx_email_settings', $settings);

        wp_send_json_success(['message' => 'Đã lưu cài đặt!']);
    }
}
