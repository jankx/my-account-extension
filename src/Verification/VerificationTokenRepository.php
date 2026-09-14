<?php
namespace Jankx\Extensions\MyAccount\Verification;

class VerificationTokenRepository
{
    const META_KEY_TOKEN = 'jankx_email_verify_token';
    const META_KEY_EXPIRES = 'jankx_email_verify_expires';
    const META_KEY_VERIFIED = 'jankx_email_verified';
    const TOKEN_LENGTH = 32;
    const TOKEN_EXPIRY = 86400; // 24 hours

    public function generateToken(int $userId): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $expires = time() + self::TOKEN_EXPIRY;

        update_user_meta($userId, self::META_KEY_TOKEN, $token);
        update_user_meta($userId, self::META_KEY_EXPIRES, $expires);

        return $token;
    }

    public function getToken(int $userId): ?string
    {
        $token = get_user_meta($userId, self::META_KEY_TOKEN, true);
        return !empty($token) ? $token : null;
    }

    public function isValidToken(int $userId, string $token): bool
    {
        $storedToken = $this->getToken($userId);
        $expires = (int) get_user_meta($userId, self::META_KEY_EXPIRES, true);

        if (!$storedToken || $storedToken !== $token) {
            return false;
        }

        if (time() > $expires) {
            $this->deleteToken($userId);
            return false;
        }

        return true;
    }

    public function deleteToken(int $userId): void
    {
        delete_user_meta($userId, self::META_KEY_TOKEN);
        delete_user_meta($userId, self::META_KEY_EXPIRES);
    }

    public function isVerified(int $userId): bool
    {
        return (bool) get_user_meta($userId, self::META_KEY_VERIFIED, true);
    }

    public function markVerified(int $userId): void
    {
        update_user_meta($userId, self::META_KEY_VERIFIED, 1);
        $this->deleteToken($userId);
    }

    public function markUnverified(int $userId): void
    {
        delete_user_meta($userId, self::META_KEY_VERIFIED);
    }
}
