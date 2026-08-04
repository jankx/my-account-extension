<?php
namespace Jankx\Extensions\MyAccount;

class AccountSidebarBlock extends Block
{
    protected $blockId = 'jankx/account-sidebar';

    public function render($attributes, $content = '', $block = null)
    {
        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-sidebar',
        ]);

        return sprintf('<aside %s>%s</aside>', $wrapperAttrs, $content);
    }
}
