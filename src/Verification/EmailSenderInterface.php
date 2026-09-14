<?php
namespace Jankx\Extensions\MyAccount\Verification;

interface EmailSenderInterface
{
    public function send(string $to, array $context): bool;
}
