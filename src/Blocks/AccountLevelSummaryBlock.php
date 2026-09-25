<?php
namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;

class AccountLevelSummaryBlock extends Block
{
    protected $blockId = 'jankx/account-level-summary';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user = wp_get_current_user();
        $showIcon = $attributes['showIcon'] ?? true;
        $showName = $attributes['showName'] ?? true;
        $showDescription = $attributes['showDescription'] ?? true;

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-account-level-summary',
        ]);

        $badge = $this->renderLevelCard($user, (bool) $showIcon, (bool) $showName, (bool) $showDescription);
        if ($badge === '') {
            return '';
        }

        return sprintf('<div %s>%s</div>', $wrapperAttrs, $badge);
    }

    protected function renderLevelCard($user, bool $showIcon, bool $showName, bool $showDescription): string
    {
        $levelSlug = get_user_meta($user->ID, 'jankx_membership_level', true) ?: 'bronze';

        $levels = [
            'bronze' => ['name' => 'Bronze', 'description' => 'New member. Accumulate points to upgrade.', 'color' => '#CD7F32'],
            'silver' => ['name' => 'Silver', 'description' => 'Exclusive deals and offers just for you.', 'color' => '#94A3B8'],
            'gold' => ['name' => 'Gold', 'description' => 'Premium benefits and VIP service.', 'color' => '#F59E0B'],
        ];

        $level = $levels[$levelSlug] ?? $levels['bronze'];

        if (!$showIcon && !$showName && !$showDescription) {
            return '';
        }

        $output = '<div class="jankx-membership-badge" style="--badge-color:' . esc_attr($level['color']) . ';background:linear-gradient(135deg,' . esc_attr($level['color']) . '15,' . esc_attr($level['color']) . '05);border:1px solid ' . esc_attr($level['color']) . '30;border-radius:12px;padding:16px;">';
        $output .= '<div style="display:flex;align-items:center;gap:12px;">';

        if ($showIcon) {
            $output .= '<div style="width:48px;height:48px;border-radius:50%;background:' . esc_attr($level['color']) . '20;display:flex;align-items:center;justify-content:center;color:' . esc_attr($level['color']) . ';flex:0 0 auto;">';
            $output .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
            $output .= '</div>';
        }

        if ($showName || $showDescription) {
            $output .= '<div style="flex:1;">';
            if ($showName) {
                $output .= '<h3 style="margin:0;font-size:16px;font-weight:700;color:' . esc_attr($level['color']) . ';">' . esc_html($level['name']) . '</h3>';
            }
            if ($showDescription) {
                $output .= '<p style="margin:4px 0 0;font-size:13px;color:#666;line-height:1.4;">' . esc_html($level['description']) . '</p>';
            }
            $output .= '</div>';
        }

        $output .= '</div></div>';

        return $output;
    }
}
