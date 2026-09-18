<?php

namespace Jankx\Extensions\MyAccount\SubPage;

class ProfileSubPage extends AbstractSubPage
{
    public function getSlug(): string
    {
        return 'profile';
    }

    public function getLabel(): string
    {
        return __('Profile', 'jankx');
    }

    public function getIcon(): string
    {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
    }

    public function getPriority(): int
    {
        return 10;
    }

    public function getContent(): string
    {
        return '<!-- wp:jankx/account-tab-profile -->'
            . '<!-- wp:jankx/profile-personal-info /-->'
            . '<!-- wp:separator {"className":"jankx-divider"} /-->'
            . '<!-- wp:jankx/profile-change-password /-->'
            . '<!-- /wp:jankx/account-tab-profile -->';
    }
}