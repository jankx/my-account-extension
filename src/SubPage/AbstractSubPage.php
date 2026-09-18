<?php

namespace Jankx\Extensions\MyAccount\SubPage;

abstract class AbstractSubPage
{
    abstract public function getSlug(): string;

    abstract public function getLabel(): string;

    abstract public function getContent(): string;

    public function getIcon(): string
    {
        return '';
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getExtension(): ?string
    {
        return null;
    }

    public function isShowInNav(): bool
    {
        return true;
    }

    public function getBadge(): string
    {
        return '';
    }

    public function getCallback()
    {
        return null;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->getLabel(),
            'icon' => $this->getIcon(),
            'priority' => $this->getPriority(),
            'extension' => $this->getExtension(),
            'show_in_nav' => $this->isShowInNav(),
            'badge' => $this->getBadge(),
            'callback' => $this->getCallback(),
            'content' => $this->getContent(),
        ];
    }
}