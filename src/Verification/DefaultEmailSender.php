<?php
namespace Jankx\Extensions\MyAccount\Verification;

class DefaultEmailSender implements EmailSenderInterface
{
    public function send(string $to, array $context): bool
    {
        $user = $context['user'] ?? null;
        $verifyUrl = $context['verify_url'] ?? '';
        $expiresHours = $context['expires_hours'] ?? 24;

        $userName = $user ? $user->display_name : '';
        $siteName = get_bloginfo('name');

        $subject = sprintf(
            '[%s] Xác nhận email tài khoản của bạn',
            $siteName
        );

        $message = $this->renderEmailTemplate([
            'user_name' => $userName,
            'verify_url' => $verifyUrl,
            'expires_hours' => $expiresHours,
            'site_name' => $siteName,
        ]);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf('From: %s <%s>', $siteName, get_option('admin_email')),
        ];

        return wp_mail($to, $subject, $message, $headers);
    }

    private function renderEmailTemplate(array $data): string
    {
        $userName = esc_html($data['user_name']);
        $verifyUrl = esc_url($data['verify_url']);
        $expiresHours = intval($data['expires_hours']);
        $siteName = esc_html($data['site_name']);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .card { background: #fff; border-radius: 12px; padding: 40px 32px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { text-align: center; margin-bottom: 32px; }
        .header h1 { font-size: 24px; color: #1a1f71; margin: 0 0 8px; }
        .content { margin-bottom: 32px; }
        .content p { margin: 0 0 16px; font-size: 15px; }
        .btn-container { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #65A30D; color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-weight: 600; font-size: 15px; }
        .btn:hover { background: #365314; }
        .note { text-align: center; font-size: 13px; color: #666; margin-top: 24px; }
        .footer { text-align: center; font-size: 12px; color: #999; margin-top: 32px; padding-top: 24px; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Xác nhận Email</h1>
            </div>
            <div class="content">
                <p>Xin chào {$userName},</p>
                <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>{$siteName}</strong>.</p>
                <p>Vui lòng nhấp vào nút bên dưới để xác nhận địa chỉ email của bạn:</p>
                <div class="btn-container">
                    <a href="{$verifyUrl}" class="btn">Xác nhận Email</a>
                </div>
                <p class="note">Liên kết xác nhận sẽ hết hạn sau {$expiresHours} giờ.</p>
            </div>
            <div class="footer">
                <p>Nếu bạn không yêu cầu email này, vui lòng bỏ qua.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
