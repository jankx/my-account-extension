<?php
namespace Jankx\Extensions\MyAccount\Verification;

class EmailVerificationService
{
    private static ?self $instance = null;

    private VerificationTokenRepository $tokenRepo;
    private EmailSenderInterface $emailSender;

    private function __construct(
        ?VerificationTokenRepository $tokenRepo = null,
        ?EmailSenderInterface $emailSender = null
    ) {
        $this->tokenRepo = $tokenRepo ?? new VerificationTokenRepository();
        $this->emailSender = $emailSender ?? new DefaultEmailSender();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function isVerified(int $userId): bool
    {
        return $this->tokenRepo->isVerified($userId);
    }

    public function sendVerificationEmail(int $userId): bool
    {
        $user = get_userdata($userId);
        if (!$user) {
            return false;
        }

        $email = $user->user_email;
        if (empty($email) || !is_email($email)) {
            return false;
        }

        $token = $this->tokenRepo->generateToken($userId);
        $verifyUrl = $this->buildVerifyUrl($userId, $token);

        return $this->emailSender->send($email, [
            'user' => $user,
            'verify_url' => $verifyUrl,
            'expires_hours' => 24,
        ]);
    }

    public function verifyEmail(int $userId, string $token): bool
    {
        if (!$this->tokenRepo->isValidToken($userId, $token)) {
            return false;
        }

        $this->tokenRepo->markVerified($userId);

        do_action('jankx_email_verified', $userId);

        return true;
    }

    public function revokeVerification(int $userId): void
    {
        $this->tokenRepo->markUnverified($userId);

        do_action('jankx_email_verification_revoked', $userId);
    }

    public function getVerificationUrl(int $userId): ?string
    {
        $token = $this->tokenRepo->getToken($userId);
        if (!$token) {
            return null;
        }

        return $this->buildVerifyUrl($userId, $token);
    }

    private function buildVerifyUrl(int $userId, string $token): string
    {
        return add_query_arg([
            'jankx_verify' => $userId,
            'token' => $token,
        ], home_url('/'));
    }

    public function getTokenRepository(): VerificationTokenRepository
    {
        return $this->tokenRepo;
    }

    public function setEmailSender(EmailSenderInterface $sender): void
    {
        $this->emailSender = $sender;
    }
}
