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
        $level = null;
        if (class_exists('\Jankx\Extensions\MembershipLevels\LevelManager')) {
            $levelManager = \Jankx\Extensions\MembershipLevels\LevelManager::getInstance();
            if ($levelManager) {
                $level = $levelManager->getUserLevelInfo($user->ID);
            }
        }

        if (!$level) {
            $levelSlug = get_user_meta($user->ID, 'jankx_membership_level', true) ?: 'bronze';
            $levels = [
                'bronze' => ['name' => 'Bronze', 'description' => 'New member. Accumulate points to upgrade.', 'color' => '#CD7F32', 'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>'],
                'silver' => ['name' => 'Silver', 'description' => 'Exclusive deals and offers just for you.', 'color' => '#94A3B8', 'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>'],
                'gold' => ['name' => 'Gold', 'description' => 'Premium benefits and VIP service.', 'color' => '#F59E0B', 'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>'],
            ];
            $level = $levels[$levelSlug] ?? $levels['bronze'];
        }

        if (!$showIcon && !$showName && !$showDescription) {
            return '';
        }

        $output = '<div class="jankx-membership-badge" style="--badge-color:' . esc_attr($level['color']) . ';background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.02);">';
        $output .= '<div style="display:flex;align-items:flex-start;gap:16px;">';

        if ($showIcon) {
            $output .= '<div style="width:48px;height:48px;border-radius:50%;border:1px solid ' . esc_attr($level['color']) . ';background:#ffffff;display:flex;align-items:center;justify-content:center;color:' . esc_attr($level['color']) . ';flex:0 0 auto;">';
            $output .= $level['icon'] ?? '';
            $output .= '</div>';
        }

        if ($showName || $showDescription) {
            $output .= '<div style="flex:1;">';
            if ($showName) {
                $output .= '<h3 style="margin:0 0 8px 0;font-size:18px;font-weight:600;color:' . esc_attr($level['color']) . ';line-height:1.2;">' . esc_html($level['name']) . '</h3>';
            }
            if ($showDescription) {
                $output .= '<p style="margin:0 0 12px 0;font-size:14px;color:#8f9bb3;line-height:1.5;max-width:280px;">' . esc_html($level['description']) . '</p>';
            }
            $output .= '<a href="#" style="display:inline-flex;align-items:center;font-size:14px;font-weight:500;color:' . esc_attr($level['color']) . ';text-decoration:none;gap:4px;">Xem chi tiết <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></a>';
            $output .= '</div>';
        }

        $output .= '</div></div>';

        return $output;
    }
}
