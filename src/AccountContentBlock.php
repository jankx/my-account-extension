<?php
namespace Jankx\Extensions\MyAccount;

use Jankx\Extensions\MyAccount\Blocks\AccountContentBodyBlock;

class AccountContentBlock extends Block
{
    protected $blockId = 'jankx/account-content';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        if (trim((string) $content) === '') {
            $content = $this->renderFallbackContent();
        }

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-content',
        ]);

        return sprintf('<div %s>%s</div>', $wrapperAttrs, $content);
    }

    protected function renderFallbackContent(): string
    {
        $user = wp_get_current_user();

        $output = '<div class="jankx-content-header">';
        $output .= '<h1 class="jankx-account-title">' . esc_html__('My Account', 'jankx') . '</h1>';
        $output .= '<p class="jankx-account-welcome">' . sprintf(__('Welcome, %s!', 'jankx'), esc_html($user->display_name)) . '</p>';
        $output .= '</div>';

        $output .= (new AccountContentBodyBlock())->getTabContent();

        return $output;
    }
}
