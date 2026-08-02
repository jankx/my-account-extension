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
            'show_profile' => true,
            'show_bookings' => true,
            'show_credits' => true,
            'show_coupons' => true,
            'show_settings' => true,
        ], $atts, self::SHORTCODE);

        $user = wp_get_current_user();
        $activeTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'profile';

        ob_start();
        $this->renderWrapperStart($user, $activeTab, $atts);
        $this->renderTabContent($activeTab, $user, $atts);
        $this->renderWrapperEnd();
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

    protected function renderWrapperStart($user, string $activeTab, array $atts): void
    {
        ?>
        <div class="jankx-my-account" data-active-tab="<?php echo esc_attr($activeTab); ?>">
            <div class="jankx-account-header">
                <div class="jankx-account-avatar">
                    <?php
                    $avatarId = get_user_meta($user->ID, 'jankx_avatar_id', true);
                    $avatarUrl = $avatarId ? wp_get_attachment_image_url($avatarId, 'medium') : get_avatar_url($user->ID, ['size' => 120]);
                    ?>
                    <img src="<?php echo esc_url($avatarUrl); ?>" alt="<?php echo esc_attr($user->display_name); ?>" class="jankx-avatar-img">
                    <button type="button" class="jankx-avatar-change" data-action="change-avatar">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        Đổi ảnh
                    </button>
                </div>
                <div class="jankx-account-info">
                    <h1 class="jankx-account-name"><?php echo esc_html($user->display_name); ?></h1>
                    <p class="jankx-account-email"><?php echo esc_html($user->user_email); ?></p>
                    <p class="jankx-account-member">
                        Thành viên từ: <?php echo esc_html(date('d/m/Y', strtotime($user->user_registered))); ?>
                    </p>
                </div>
            </div>

            <nav class="jankx-account-nav">
                <?php
                $tabs = [
                    'profile' => 'Hồ sơ',
                    'bookings' => 'Lịch sử đặt tour',
                ];

                if ($atts['show_credits'] && $this->isExtensionActive('user-credits')) {
                    $tabs['credits'] = 'Số dư tín dụng';
                }

                if ($atts['show_coupons'] && $this->isExtensionActive('coupon-system')) {
                    $tabs['coupons'] = 'Mã giảm giá';
                }

                if ($atts['show_settings']) {
                    $tabs['settings'] = 'Cài đặt';
                }

                foreach ($tabs as $tabId => $tabLabel) :
                ?>
                <a href="?tab=<?php echo esc_attr($tabId); ?>"
                   class="jankx-nav-tab <?php echo $activeTab === $tabId ? 'jankx-nav-tab-active' : ''; ?>"
                   data-tab="<?php echo esc_attr($tabId); ?>">
                    <?php echo esc_html($tabLabel); ?>
                </a>
                <?php endforeach; ?>
            </nav>

            <div class="jankx-account-content">
        <?php
    }

    protected function renderTabContent(string $activeTab, $user, array $atts): void
    {
        switch ($activeTab) {
            case 'bookings':
                $this->renderBookingsTab($user);
                break;
            case 'credits':
                $this->renderCreditsTab($user);
                break;
            case 'coupons':
                $this->renderCouponsTab($user);
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

    protected function renderWrapperEnd(): void
    {
        ?>
            </div>
        </div>
        <?php
    }

    protected function renderProfileTab($user): void
    {
        $phone = get_user_meta($user->ID, 'phone', true);
        ?>
        <div class="jankx-tab-panel jankx-tab-profile" id="jankx-tab-profile">
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

    protected function renderBookingsTab($user): void
    {
        $bookings = $this->getUserBookings($user->ID);
        ?>
        <div class="jankx-tab-panel jankx-tab-bookings" id="jankx-tab-bookings">
            <h2 class="jankx-section-title">Lịch sử đặt tour</h2>

            <?php if (empty($bookings)) : ?>
                <div class="jankx-empty-state">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <p>Bạn chưa có đơn đặt tour nào.</p>
                    <a href="<?php echo esc_url(home_url('/danh-sach-tour')); ?>" class="jankx-btn jankx-btn-outline">
                        Khám phá tour ngay
                    </a>
                </div>
            <?php else : ?>
                <div class="jankx-table-responsive">
                    <table class="jankx-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Tour</th>
                                <th>Ngày khởi hành</th>
                                <th>Số lượng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking) :
                                $status = get_post_meta($booking->ID, '_booking_status', true);
                                $statusClass = $this->getStatusClass($status);
                                $total = get_post_meta($booking->ID, '_booking_total', true);
                                $departureDate = get_post_meta($booking->ID, '_departure_date', true);
                                $quantity = get_post_meta($booking->ID, '_booking_quantity', true);
                            ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo esc_html($booking->post_title ?: $booking->ID); ?></strong>
                                </td>
                                <td>
                                    <?php
                                    $tourId = get_post_meta($booking->ID, '_tour_id', true);
                                    if ($tourId) :
                                        $tourTitle = get_the_title($tourId);
                                        $tourUrl = get_permalink($tourId);
                                    ?>
                                    <a href="<?php echo esc_url($tourUrl); ?>" class="jankx-tour-link">
                                        <?php echo esc_html($tourTitle); ?>
                                    </a>
                                    <?php else : ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $departureDate ? esc_html(date('d/m/Y', strtotime($departureDate))) : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td><?php echo esc_html($quantity ?: '—'); ?></td>
                                <td>
                                    <strong class="jankx-price">
                                        <?php echo $total ? number_format((float)$total, 0, ',', '.') . '₫' : '—'; ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="jankx-badge <?php echo esc_attr($statusClass); ?>">
                                        <?php echo esc_html($this->getStatusLabel($status)); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function renderCreditsTab($user): void
    {
        if (!$this->isExtensionActive('user-credits')) {
            return;
        }

        $balance = $this->getUserCredits($user->ID);
        ?>
        <div class="jankx-tab-panel jankx-tab-credits" id="jankx-tab-credits">
            <h2 class="jankx-section-title">Số dư tín dụng</h2>

            <div class="jankx-credit-card">
                <div class="jankx-credit-label">Số dư hiện tại</div>
                <div class="jankx-credit-amount">
                    <?php echo number_format((float)$balance, 0, ',', '.'); ?>₫
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
                                    <?php echo ($item->amount > 0 ? '+' : '') . number_format((float)$item->amount, 0, ',', '.'); ?>₫
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

    protected function renderCouponsTab($user): void
    {
        if (!$this->isExtensionActive('coupon-system')) {
            return;
        }

        $coupons = $this->getUserCoupons($user->ID);
        ?>
        <div class="jankx-tab-panel jankx-tab-coupons" id="jankx-tab-coupons">
            <h2 class="jankx-section-title">Mã giảm giá của bạn</h2>

            <?php if (empty($coupons)) : ?>
                <div class="jankx-empty-state">
                    <p>Bạn chưa có mã giảm giá nào.</p>
                </div>
            <?php else : ?>
                <div class="jankx-coupon-grid">
                    <?php foreach ($coupons as $coupon) :
                        $expiry = get_post_meta($coupon->ID, '_coupon_expiry', true);
                        $isExpired = $expiry && strtotime($expiry) < time();
                    ?>
                    <div class="jankx-coupon-item <?php echo $isExpired ? 'jankx-coupon-expired' : ''; ?>">
                        <div class="jankx-coupon-badge">
                            <?php
                            $discountType = get_post_meta($coupon->ID, '_coupon_discount_type', true);
                            $discountValue = get_post_meta($coupon->ID, '_coupon_discount_value', true);
                            if ($discountType === 'percent') :
                                echo esc_html($discountValue) . '%';
                            else :
                                echo number_format((float)$discountValue, 0, ',', '.') . '₫';
                            endif;
                            ?>
                        </div>
                        <div class="jankx-coupon-info">
                            <div class="jankx-coupon-code"><?php echo esc_html($coupon->post_title); ?></div>
                            <div class="jankx-coupon-desc"><?php echo esc_html($coupon->post_content ?: 'Áp dụng cho tour'); ?></div>
                            <?php if ($expiry) : ?>
                            <div class="jankx-coupon-expiry">
                                HSD: <?php echo esc_html(date('d/m/Y', strtotime($expiry))); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    protected function renderSettingsTab($user): void
    {
        ?>
        <div class="jankx-tab-panel jankx-tab-settings" id="jankx-tab-settings">
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

    protected function getUserBookings(int $userId): array
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

    protected function getUserCredits(int $userId)
    {
        if ($this->isExtensionActive('user-credits')) {
            return get_user_meta($userId, 'jankx_credits', true) ?: 0;
        }
        return 0;
    }

    protected function getCreditHistory(int $userId): array
    {
        if ($this->isExtensionActive('user-credits')) {
            return get_user_meta($userId, 'jankx_credit_history', true) ?: [];
        }
        return [];
    }

    protected function getUserCoupons(int $userId): array
    {
        if (!$this->isExtensionActive('coupon-system')) {
            return [];
        }

        return get_posts([
            'post_type' => 'jankx_coupon',
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => '_assigned_users',
                    'value' => $userId,
                    'compare' => 'LIKE',
                ],
            ],
            'posts_per_page' => -1,
        ]);
    }

    protected function isExtensionActive(string $extensionSlug): bool
    {
        $activeExtensions = get_option('jankx_active_extensions', []);
        return in_array($extensionSlug, $activeExtensions);
    }
}
