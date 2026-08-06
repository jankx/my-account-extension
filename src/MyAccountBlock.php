<?php
namespace Jankx\Extensions\MyAccount;

class MyAccountBlock extends Block
{
    protected $blockId = 'jankx/my-account';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return $this->renderLoginPrompt();
        }

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-my-account-block',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);
        $output .= $content;
        $output .= '</div>';

        return $output;
    }

    protected function renderLoginPrompt(): string
    {
        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-my-account-block jankx-logged-out',
        ]);

        return sprintf(
            '<div %s>
                <div class="jankx-login-prompt">
                    <div class="jankx-login-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h2>Please Login</h2>
                    <p>You need to login to access your account.</p>
                    <a href="%s" class="jankx-btn jankx-btn-primary">Login Now</a>
                </div>
            </div>',
            $wrapperAttrs,
            esc_url(wp_login_url(get_permalink()))
        );
    }
}
