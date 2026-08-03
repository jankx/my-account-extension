<?php
/**
 * Account Tab Orders Block
 *
 * @package Jankx\Gutenberg\Blocks
 */

namespace Jankx\Extensions\MyAccount\Blocks;

use Jankx\Extensions\MyAccount\Block;

class AccountTabOrdersBlock extends Block
{
    protected $blockId = 'jankx/account-tab-orders';

    public function render($attributes, $content = '', $block = null)
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
        }

        if ($activeTab !== 'orders') {
            return '';
        }

        $user = wp_get_current_user();
        $orders = $this->getUserOrders($user->ID);

        $wrapperAttrs = get_block_wrapper_attributes([
            'class' => 'jankx-tab-panel jankx-tab-orders',
        ]);

        $output = sprintf('<div %s>', $wrapperAttrs);
        $output .= '<h2 class="jankx-section-title">Your Orders</h2>';

        if (empty($orders)) {
            $output .= '<div class="jankx-empty-state">';
            $output .= '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
            $output .= '<p>You have no orders yet.</p>';
            $output .= '<a href="' . esc_url(home_url('/danh-sach-tour')) . '" class="jankx-btn jankx-btn-outline">Explore Tours</a>';
            $output .= '</div>';
        } else {
            $output .= '<div class="jankx-orders-list">';
            foreach ($orders as $order) {
                $status = get_post_meta($order->ID, '_booking_status', true);
                $total = get_post_meta($order->ID, '_booking_total', true);
                $departureDate = get_post_meta($order->ID, '_departure_date', true);
                $quantity = get_post_meta($order->ID, '_booking_quantity', true);
                $tourId = get_post_meta($order->ID, '_tour_id', true);
                $tourTitle = $tourId ? get_the_title($tourId) : 'N/A';
                $tourImage = $tourId ? get_the_post_thumbnail_url($tourId, 'thumbnail') : '';

                $output .= '<div class="jankx-order-card">';
                $output .= '<div class="jankx-order-image">';
                if ($tourImage) {
                    $output .= '<img src="' . esc_url($tourImage) . '" alt="' . esc_attr($tourTitle) . '">';
                }
                $output .= '</div>';
                $output .= '<div class="jankx-order-info">';
                $output .= '<h3 class="jankx-order-title">' . esc_html($tourTitle) . '</h3>';
                $output .= '<p class="jankx-order-meta">';
                $output .= '<span class="jankx-order-date">' . ($departureDate ? esc_html(date('d/m/Y', strtotime($departureDate))) : '—') . '</span>';
                $output .= '<span class="jankx-order-qty">Qty: ' . esc_html($quantity ?: '1') . '</span>';
                $output .= '</p>';
                $output .= '</div>';
                if ($total) {
                    $output .= '<div class="jankx-order-price"><span class="jankx-price-amount">' . number_format((float)$total, 0, ',', '.') . 'đ</span></div>';
                }
                $output .= '<div class="jankx-order-status"><span class="jankx-badge jankx-badge-' . esc_attr($status) . '">' . esc_html(ucfirst($status)) . '</span></div>';
                $output .= '</div>';
            }
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    protected function getUserOrders(int $userId): array
    {
        $postTypes = ['jankx_booking', 'booking'];
        $foundPostType = null;

        foreach ($postTypes as $pt) {
            if (post_type_exists($pt)) {
                $foundPostType = $pt;
                break;
            }
        }

        if (!$foundPostType) {
            return [];
        }

        $query = new \WP_Query([
            'post_type' => $foundPostType,
            'post_status' => ['publish', 'pending', 'completed'],
            'meta_query' => [
                [
                    'key' => '_customer_id',
                    'value' => $userId,
                    'compare' => '=',
                ],
            ],
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        return $query->posts;
    }
}
