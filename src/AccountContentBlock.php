<?php
namespace Jankx\Extensions\MyAccount;

class AccountContentBlock extends Block
{
    protected $blockId = 'jankx/account-content';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user = wp_get_current_user();
        $activeTab = $this->getActiveTab();

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-content',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);

        // Welcome header
        $output .= '<div class="jankx-content-header">';
        $output .= '<h1 class="jankx-account-title">' . esc_html__('My Account', 'jankx') . '</h1>';
        $output .= '<p class="jankx-account-welcome">' . sprintf(__('Welcome, %s!', 'jankx'), esc_html($user->display_name)) . '</p>';
        $output .= '</div>';

        // Render active tab
        $output .= $this->renderTab($activeTab);

        $output .= '</div>';

        return $output;
    }

    protected function getActiveTab(): string
    {
        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
        }
        return $activeTab;
    }

    protected function renderTab(string $tab): string
    {
        $block = new AccountTabProfileBlock();
        switch ($tab) {
            case 'orders':
                if (class_exists(\Jankx\Extensions\Travel\Blocks\AccountTabOrdersBlock::class)) {
                    $ordersBlock = new \Jankx\Extensions\Travel\Blocks\AccountTabOrdersBlock();
                    return $ordersBlock->render([]);
                }
                return $this->renderEmptyTab('orders');

            case 'coupons':
                if (class_exists(\Jankx\Extensions\CouponSystem\Blocks\AccountTabCouponsBlock::class)) {
                    $couponsBlock = new \Jankx\Extensions\CouponSystem\Blocks\AccountTabCouponsBlock();
                    return $couponsBlock->render([]);
                }
                return $this->renderEmptyTab('coupons');

            case 'credits':
                if (class_exists(\Jankx\Extensions\UserCredits\Blocks\AccountTabCreditsBlock::class)) {
                    $creditsBlock = new \Jankx\Extensions\UserCredits\Blocks\AccountTabCreditsBlock();
                    return $creditsBlock->render([]);
                }
                return $this->renderEmptyTab('credits');

            case 'profile':
            default:
                return $block->render([]);
        }
    }

    protected function renderEmptyTab(string $tab): string
    {
        return sprintf(
            '<div class="jankx-tab-panel jankx-tab-%s"><p class="text-muted">%s</p></div>',
            esc_attr($tab),
            esc_html__('This feature is coming soon.', 'jankx')
        );
    }
}
