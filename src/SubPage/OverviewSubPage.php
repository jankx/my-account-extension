<?php

namespace Jankx\Extensions\MyAccount\SubPage;

class OverviewSubPage extends AbstractSubPage
{
    public function getSlug(): string
    {
        return 'overview';
    }

    public function getLabel(): string
    {
        return __('Tổng quan', 'jankx');
    }

    public function getIcon(): string
    {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>';
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function getContent(): string
    {
        return '<!-- wp:jankx/account-tab-overview /-->';
    }
}