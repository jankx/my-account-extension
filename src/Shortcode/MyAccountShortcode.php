<?php
namespace Jankx\Extensions\MyAccount\Shortcode;

class MyAccountShortcode
{
    const SHORTCODE = 'jankx_my_account';

    public function register(): void
    {
        add_shortcode(self::SHORTCODE, [$this, 'render']);
    }

    public function render($atts): string
    {
        if (!is_user_logged_in()) {
            return $this->renderLoginPrompt();
        }

        $atts = shortcode_atts([
            'show_sidebar' => true,
        ], $atts, self::SHORTCODE);

        $user = wp_get_current_user();
        
        // Detect active tab from query var (sub-page) or GET param
        $activeTab = get_query_var('jankx_account_page');
        if (empty($activeTab)) {
            $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';
        }

        ob_start();
        $this->renderLayout($user, $activeTab, $atts);
        return ob_get_clean();
    }

    protected function renderLoginPrompt(): string
    {
        return '<div class="jankx-account-login-prompt">
            <div class="jankx-account-login-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#65A30D" stroke-width="1.5">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <h2>Vui lòng đăng nhập</h2>
            <p>Bạn cần đăng nhập để truy cập trang tài khoản.</p>
            <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="jankx-btn jankx-btn-primary">
                Đăng nhập ngay
            </a>
        </div>';
    }

    protected function renderLayout($user, string $activeTab, array $atts): void
    {
        ?>
        <div class="jankx-account-layout">
            <!-- Sidebar -->
            <aside class="jankx-account-sidebar">
                <!-- Sidebar Header -->
                <div class="jankx-sidebar-header">
                    <div class="jankx-avatar-wrapper">
                        <?php
                        $avatarId = get_user_meta($user->ID, 'jankx_avatar_id', true);
                        $avatarUrl = $avatarId ? wp_get_attachment_image_url($avatarId, 'medium') : get_avatar_url($user->ID, ['size' => 120]);
                        ?>
                        <img src="<?php echo esc_url($avatarUrl); ?>" 
                             alt="<?php echo esc_attr($user->display_name); ?>" 
                             class="jankx-avatar-img">
                        <button type="button" class="jankx-avatar-change" data-action="change-avatar">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                        </button>
                    </div>
                    <h2 class="jankx-user-name"><?php echo esc_html($user->display_name); ?></h2>
                    <a href="?tab=profile" class="jankx-edit-profile-link">
                        Cập nhật thông tin cá nhân
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                </div>

                <!-- Membership Badge -->
                <?php $this->renderMembershipBadge($user); ?>

                <!-- Sidebar Navigation -->
                <nav class="jankx-sidebar-nav">
                    <?php $this->renderSidebarNav($activeTab); ?>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="jankx-account-content">
                <?php $this->renderTabContent($activeTab, $user); ?>
            </main>
        </div>
        <?php
    }

    protected function renderMembershipBadge($user): void
    {
        $userLevel = get_user_meta($user->ID, 'jankx_user_level', true) ?: 'bronze';
        
        $levels = [
            'bronze' => [
                'name' => 'Hạng Đồng',
                'description' => 'Thành viên mới, tích lũy điểm để nâng hạng.',
                'color' => '#CD7F32',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
            ],
            'silver' => [
                'name' => 'Hạng Bạc',
                'description' => 'Nhận thêm ưu đãi để hướng các ưu đãi độc quyền dành riêng cho bạn.',
                'color' => '#65A30D',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
            ],
            'gold' => [
                'name' => 'Hạng Vàng',
                'description' => 'Ưu đãi cao nhất và dịch vụ VIP.',
                'color' => '#F59E0B',
                'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
            ],
        ];

        $level = $levels[$userLevel] ?? $levels['bronze'];
        ?>
        <div class="jankx-membership-badge" style="--badge-color: <?php echo esc_attr($level['color']); ?>">
            <div class="jankx-badge-icon">
                <?php echo $level['icon']; ?>
            </div>
            <div class="jankx-badge-content">
                <h3 class="jankx-badge-name"><?php echo esc_html($level['name']); ?></h3>
                <p class="jankx-badge-desc"><?php echo esc_html($level['description']); ?></p>
                <a href="#" class="jankx-badge-link">
                    Xem chi tiết
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </a>
            </div>
        </div>
        <?php
    }

    protected function renderSidebarNav(string $activeTab): void
    {
        $subPages = \Jankx\Extensions\MyAccount\MyAccountExtension::getSubPages();

        foreach ($subPages as $slug => $page) {
            // Skip if extension is not active
            if (!empty($page['extension']) && !$this->isExtensionActive($page['extension'])) {
                continue;
            }

            // Skip if not shown in nav
            if (empty($page['show_in_nav'])) {
                continue;
            }

            $isActive = $activeTab === $slug;
            $url = $this->getSubPageUrl($slug);
            ?>
            <a href="<?php echo esc_url($url); ?>" 
               class="jankx-nav-item <?php echo $isActive ? 'jankx-nav-active' : ''; ?>"
               data-tab="<?php echo esc_attr($slug); ?>">
                <span class="jankx-nav-icon"><?php echo $page['icon'] ?? ''; ?></span>
                <span class="jankx-nav-label"><?php echo esc_html($page['label']); ?></span>
                <?php if (!empty($page['badge'])): ?>
                    <span class="jankx-nav-badge"><?php echo esc_html($page['badge']); ?></span>
                <?php endif; ?>
            </a>
            <?php
        }
    }

    /**
     * Get URL for a sub-page
     */
    protected function getSubPageUrl(string $subPage): string
    {
        $pageId = get_option('jankx_my_account_page_id', 0);
        if ($pageId) {
            $baseUrl = get_permalink($pageId);
            if ($baseUrl) {
                return rtrim($baseUrl, '/') . '/' . $subPage . '/';
            }
        }
        
        // Fallback to query string
        return '?tab=' . $subPage;
    }

    protected function renderTabContent(string $activeTab, $user): void
    {
        $subPage = \Jankx\Extensions\MyAccount\MyAccountExtension::getSubPage($activeTab);
        
        if ($subPage && isset($subPage['callback']) && is_callable($subPage['callback'])) {
            call_user_func($subPage['callback'], $user);
            return;
        }

        // Fallback to built-in tab rendering
        switch ($activeTab) {
            case 'coupons':
                $this->renderCouponsTab($user);
                break;
            case 'credits':
                $this->renderCreditsTab($user);
                break;
            case 'orders':
                $this->renderOrdersTab($user);
                break;
            case 'reviews':
                $this->renderReviewsTab($user);
                break;
            case 'login-history':
                $this->renderLoginHistoryTab($user);
                break;
            case 'settings':
                $this->renderSettingsTab($user);
                break;
            case 'profile':
            default:
                $this->renderProfileTab($user);
                break;
        }
    }

    protected function renderProfileTab($user): void
    {
        $phone = get_user_meta($user->ID, 'phone', true);
        ?>
        <div class="jankx-tab-panel jankx-tab-profile">
            <h2 class="jankx-section-title">Thông tin cá nhân</h2>
            <form id="jankx-profile-form" class="jankx-form">
                <div class="jankx-form-group">
                    <label for="jankx-display-name">Họ và tên</label>
                    <input type="text" id="jankx-display-name" name="display_name"
                           value="<?php echo esc_attr($user->display_name); ?>" required>
                </div>

                <div class="jankx-form-row">
                    <div class="jankx-form-group">
                        <label for="jankx-email">Email</label>
                        <input type="email" id="jankx-email" name="email"
                               value="<?php echo esc_attr($user->user_email); ?>" required>
                    </div>
                    <div class="jankx-form-group">
                        <label for="jankx-phone">Số điện thoại</label>
                        <input type="tel" id="jankx-phone" name="phone"
                               value="<?php echo esc_attr($phone); ?>"
                               placeholder="Ví dụ: 0912345678">
                    </div>
                </div>

                <div class="jankx-form-actions">
                    <button type="submit" class="jankx-btn jankx-btn-primary" id="jankx-save-profile">
                        Lưu thay đổi
                    </button>
                    <span class="jankx-form-status" id="jankx-profile-status"></span>
                </div>
            </form>

            <div class="jankx-divider"></div>

            <h2 class="jankx-section-title">Đổi mật khẩu</h2>
            <form id="jankx-password-form" class="jankx-form">
                <div class="jankx-form-group">
                    <label for="jankx-current-password">Mật khẩu hiện tại</label>
                    <input type="password" id="jankx-current-password" name="current_password" required>
                </div>

                <div class="jankx-form-row">
                    <div class="jankx-form-group">
                        <label for="jankx-new-password">Mật khẩu mới</label>
                        <input type="password" id="jankx-new-password" name="new_password"
                               minlength="8" required>
                    </div>
                    <div class="jankx-form-group">
                        <label for="jankx-confirm-password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="jankx-confirm-password" name="confirm_password"
                               minlength="8" required>
                    </div>
                </div>

                <div class="jankx-form-actions">
                    <button type="submit" class="jankx-btn jankx-btn-primary" id="jankx-change-password">
                        Đổi mật khẩu
                    </button>
                    <span class="jankx-form-status" id="jankx-password-status"></span>
                </div>
            </form>
        </div>
        <?php
    }

    protected function renderOrdersTab($user): void
    {
        $orders = $this->getUserOrders($user->ID);
        ?>
        <div class="jankx-tab-panel jankx-tab-orders">
            <h2 class="jankx-section-title">Đơn hàng của bạn</h2>

            <?php if (empty($orders)) : ?>
                <div class="jankx-empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <p>Bạn chưa có đơn hàng nào.</p>
                    <a href="<?php echo esc_url(home_url('/danh-sach-tour')); ?>" class="jankx-btn jankx-btn-outline">
                        Khám phá tour ngay
                    </a>
                </div>
            <?php else : ?>
                <div class="jankx-orders-list">
                    <?php foreach ($orders as $order) :
                        $status = get_post_meta($order->ID, '_booking_status', true);
                        $statusClass = $this->getStatusClass($status);
                        $total = get_post_meta($order->ID, '_booking_total', true);
                        $departureDate = get_post_meta($order->ID, '_departure_date', true);
                        $quantity = get_post_meta($order->ID, '_booking_quantity', true);
                        $tourId = get_post_meta($order->ID, '_tour_id', true);
                        $tourTitle = $tourId ? get_the_title($tourId) : 'N/A';
                        $tourImage = $tourId ? get_the_post_thumbnail_url($tourId, 'thumbnail') : '';
                    ?>
                    <div class="jankx-order-card">
                        <div class="jankx-order-image">
                            <?php if ($tourImage): ?>
                                <img src="<?php echo esc_url($tourImage); ?>" alt="<?php echo esc_attr($tourTitle); ?>">
                            <?php else: ?>
                                <div class="jankx-order-placeholder">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="jankx-order-info">
                            <h3 class="jankx-order-title"><?php echo esc_html($tourTitle); ?></h3>
                            <p class="jankx-order-meta">
                                <span class="jankx-order-date">
                                    <?php echo $departureDate ? esc_html(date('d/m/Y', strtotime($departureDate))) : '—'; ?>
                                </span>
                                <span class="jankx-order-qty">Số lượng: <?php echo esc_html($quantity ?: '1'); ?></span>
                            </p>
                        </div>
                        <div class="jankx-order-price">
                            <?php if ($total): ?>
                                <span class="jankx-price-amount"><?php echo number_format((float)$total, 0, ',', '.'); ?>đ</span>
                            <?php endif; ?>
                        </div>
                        <div class="jankx-order-status">
                            <span class="jankx-badge <?php echo esc_attr($statusClass); ?>">
                                <?php echo esc_html($this->getStatusLabel($status)); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function renderReviewsTab($user): void
    {
        ?>
        <div class="jankx-tab-panel jankx-tab-reviews">
            <h2 class="jankx-section-title">Đánh giá của bạn</h2>
            <div class="jankx-empty-state">
                <p>Bạn chưa có đánh giá nào.</p>
            </div>
        </div>
        <?php
    }

    protected function renderCouponsTab($user): void
    {
        if (!$this->isExtensionActive('coupon-system')) {
            return;
        }
        ?>
        <div class="jankx-tab-panel jankx-tab-coupons">
            <h2 class="jankx-section-title">Mã ưu đãi của bạn</h2>
            <div class="jankx-empty-state">
                <p>Bạn chưa có mã ưu đãi nào.</p>
            </div>
        </div>
        <?php
    }

    protected function renderCreditsTab($user): void
    {
        if (!$this->isExtensionActive('user-credits')) {
            return;
        }

        $balance = $this->getUserCredits();
        ?>
        <div class="jankx-tab-panel jankx-tab-credits">
            <h2 class="jankx-section-title">Xu của bạn</h2>
            
            <div class="jankx-credit-card">
                <div class="jankx-credit-label">Số dư hiện tại</div>
                <div class="jankx-credit-amount">
                    <?php echo number_format((float)$balance, 0, ',', '.'); ?> XU
                </div>
            </div>

            <div class="jankx-credit-history">
                <h3>Lịch sử giao dịch</h3>
                <?php $history = $this->getCreditHistory($user->ID); ?>
                <?php if (empty($history)) : ?>
                    <p class="text-muted">Chưa có giao dịch nào.</p>
                <?php else : ?>
                    <table class="jankx-table">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Mô tả</th>
                                <th>Số tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $item) : ?>
                            <tr>
                                <td><?php echo esc_html(date('d/m/Y H:i', strtotime($item->date))); ?></td>
                                <td><?php echo esc_html($item->description); ?></td>
                                <td class="<?php echo $item->amount > 0 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo ($item->amount > 0 ? '+' : '') . number_format((float)$item->amount, 0, ',', '.'); ?> XU
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    protected function renderLoginHistoryTab($user): void
    {
        ?>
        <div class="jankx-tab-panel jankx-tab-login-history">
            <h2 class="jankx-section-title">Quản lý đăng nhập</h2>
            <p class="text-muted">Tính năng đang được phát triển.</p>
        </div>
        <?php
    }

    protected function renderSettingsTab($user): void
    {
        ?>
        <div class="jankx-tab-panel jankx-tab-settings">
            <h2 class="jankx-section-title">Cài đặt tài khoản</h2>

            <div class="jankx-settings-section">
                <h3>Thông báo email</h3>
                <form class="jankx-form">
                    <label class="jankx-checkbox">
                        <input type="checkbox" name="email_booking" checked>
                        <span>Nhận thông báo đặt tour qua email</span>
                    </label>
                    <label class="jankx-checkbox">
                        <input type="checkbox" name="email_promotions">
                        <span>Nhận thông tin khuyến mãi</span>
                    </label>
                    <label class="jankx-checkbox">
                        <input type="checkbox" name="email_newsletter">
                        <span>Đăng ký nhận bản tin</span>
                    </label>
                </form>
            </div>

            <div class="jankx-divider"></div>

            <div class="jankx-settings-section">
                <h3>Xóa tài khoản</h3>
                <p class="text-muted">
                    Khi xóa tài khoản, tất cả dữ liệu của bạn sẽ bị xóa vĩnh viễn.
                    Hành động này không thể hoàn tác.
                </p>
                <button type="button" class="jankx-btn jankx-btn-danger" id="jankx-delete-account">
                    Xóa tài khoản
                </button>
            </div>
        </div>
        <?php
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
            'posts_per_page' => get_option('jankx_my_account_booking_per_page', 10),
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        return $query->posts;
    }

    protected function getCouponCount(): int
    {
        if (!$this->isExtensionActive('coupon-system')) {
            return 0;
        }

        return count(get_posts([
            'post_type' => 'jankx_coupon',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]));
    }

    protected function getUserCredits(): float
    {
        if (!$this->isExtensionActive('user-credits')) {
            return 0;
        }

        return (float) get_user_meta(get_current_user_id(), 'jankx_credits', true) ?: 0;
    }

    protected function getCreditHistory(int $userId): array
    {
        if (!$this->isExtensionActive('user-credits')) {
            return [];
        }

        return get_user_meta($userId, 'jankx_credit_history', true) ?: [];
    }

    protected function getOrderCount(): int
    {
        return count($this->getUserOrders(get_current_user_id()));
    }

    protected function getStatusClass(string $status): string
    {
        $classes = [
            'pending' => 'jankx-badge-warning',
            'confirmed' => 'jankx-badge-info',
            'completed' => 'jankx-badge-success',
            'cancelled' => 'jankx-badge-danger',
            'paid' => 'jankx-badge-success',
        ];

        return $classes[$status] ?? 'jankx-badge-secondary';
    }

    protected function getStatusLabel(string $status): string
    {
        $labels = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'paid' => 'Đã thanh toán',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    protected function isExtensionActive(string $extensionSlug): bool
    {
        $activeExtensions = get_option('jankx_active_extensions', []);
        return in_array($extensionSlug, $activeExtensions);
    }
}
