<?php
namespace Jankx\Extensions\MyAccount;

class AccountContentBlock extends Block
{
    protected $blockId = 'jankx/account-content';

    public function render($attributes, $content = '', $block = null)
    {
        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-content',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);
        $output .= $content;
        $output .= '</div>';

        return $output;
    }
}
