<?php
namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;

class AccountContentFooterBlock extends Block
{
    protected $blockId = 'jankx/account-content-footer';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in() || trim((string) $content) === '') {
            return '';
        }

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-content-footer',
        ]);

        return sprintf('<div %s>%s</div>', $wrapperAttrs, $content);
    }
}
