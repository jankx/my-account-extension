<?php
namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\AccountTabProfileBlock;
use Jankx\Extensions\MyAccount\Block;
use Jankx\Extensions\MyAccount\MyAccountExtension;

class AccountContentBodyBlock extends Block
{
    protected $blockId = 'jankx/account-content-body';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-content-body',
        ]);

        return sprintf(
            '<div %s>%s%s</div>',
            $wrapperAttrs,
            $this->getTabContent(),
            $content
        );
    }

    public function getTabContent(): string
    {
        return $this->renderTab($this->getActiveTab());
    }

    protected function getActiveTab(): string
    {
        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'overview';
        }
        return $activeTab;
    }

    protected function renderTab(string $tab): string
    {
        $subPage = MyAccountExtension::getSubPage($tab);
        if ($subPage && !empty($subPage['post_id'])) {
            $rendered = \Jankx\Extensions\MyAccount\SubPage\SubPageManager::get_instance()->renderPostContent($subPage['post_id']);
            if ($rendered !== '') {
                return $rendered;
            }
        }

        switch ($tab) {
            case 'orders':
                if (class_exists(\Jankx\Extensions\Ecommerce\Blocks\AccountTabOrdersBlock::class)) {
                    $ordersBlock = new \Jankx\Extensions\Ecommerce\Blocks\AccountTabOrdersBlock();
                    return $ordersBlock->render([]);
                }
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
                $block = new AccountTabProfileBlock();
                return $block->render([]);

            case 'reviews':
                if (class_exists(\Jankx\Extensions\ReviewSystem\Blocks\ReviewsPendingBlock::class)) {
                    $pendingBlock = new \Jankx\Extensions\ReviewSystem\Blocks\ReviewsPendingBlock();
                    $completedBlock = new \Jankx\Extensions\ReviewSystem\Blocks\ReviewsCompletedBlock();
                    $myReviewsBlock = new \Jankx\Extensions\ReviewSystem\Blocks\ReviewsMyReviewsBlock();

                    $output = '<div class="jankx-tab-panel jankx-tab-reviews">';
                    $output .= $myReviewsBlock->render([]);
                    $output .= $pendingBlock->render([]);
                    $output .= $completedBlock->render([]);
                    $output .= '</div>';
                    return $output;
                }
                return $this->renderEmptyTab('reviews');

            default:
                $subPage = MyAccountExtension::getSubPage($tab);
                if ($subPage && !empty($subPage['callback']) && is_callable($subPage['callback'])) {
                    return call_user_func($subPage['callback'], wp_get_current_user());
                }
                return $this->renderEmptyTab($tab);
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
