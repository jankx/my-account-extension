<?php
namespace Jankx\Extensions\MyAccount;

class ContentHeaderBlock extends Block
{
    protected $blockId = 'jankx/content-header';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user = wp_get_current_user();
        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-content-header',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);
        $output .= '<h1 class="jankx-account-title">' . esc_html__('My Account', 'jankx') . '</h1>';
        $output .= '<p class="jankx-account-welcome">' . sprintf(__('Welcome, %s!', 'jankx'), esc_html($user->display_name)) . '</p>';
        $output .= '</div>';

        return $output;
    }
}
