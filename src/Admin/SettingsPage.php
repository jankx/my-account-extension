<?php
namespace Jankx\Extensions\MyAccount\Admin;

class SettingsPage
{
    const PAGE_SLUG = 'jankx-my-account-settings';
    const OPTION_GROUP = 'jankx_my_account_settings';

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenu'], 25);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenu(): void
    {
        add_submenu_page(
            'jankx-theme-options',
            __('My Account Settings', 'jankx'),
            __('My Account', 'jankx'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'renderPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting(self::OPTION_GROUP, 'jankx_my_account_page_id', [
            'default' => 0,
            'sanitize_callback' => 'absint',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_enable_profile', [
            'default' => 1,
            'sanitize_callback' => 'absint',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_enable_bookings', [
            'default' => 1,
            'sanitize_callback' => 'absint',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_enable_credits', [
            'default' => 1,
            'sanitize_callback' => 'absint',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_enable_coupons', [
            'default' => 1,
            'sanitize_callback' => 'absint',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_enable_settings', [
            'default' => 1,
            'sanitize_callback' => 'absint',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_redirect_after_login', [
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_avatar_size', [
            'default' => 120,
            'sanitize_callback' => 'absint',
        ]);

        register_setting(self::OPTION_GROUP, 'jankx_my_account_booking_per_page', [
            'default' => 10,
            'sanitize_callback' => 'absint',
        ]);
    }

    public function enqueueAssets(string $hook): void
    {
        if (strpos($hook, self::PAGE_SLUG) === false) {
            return;
        }

        wp_enqueue_media();
    }

    public function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('My Account Settings', 'jankx'); ?></h1>
            <p class="description"><?php esc_html_e('Cài đặt trang tài khoản người dùng.', 'jankx'); ?></p>

            <form method="post" action="options.php" style="max-width: 700px; margin-top: 20px;">
                <?php settings_fields(self::OPTION_GROUP); ?>

                <h2><?php esc_html_e('Cài đặt chung', 'jankx'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="jankx_my_account_page_id"><?php esc_html_e('Trang My Account', 'jankx'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_dropdown_posts([
                                'name' => 'jankx_my_account_page_id',
                                'id' => 'jankx_my_account_page_id',
                                'selected' => get_option('jankx_my_account_page_id', 0),
                                'show_option_none' => __('— Chọn trang —', 'jankx'),
                                'option_none_value' => 0,
                                'post_type' => 'page',
                                'class' => 'regular-text',
                            ]);
                            ?>
                            <p class="description"><?php esc_html_e('Chọn trang chứa shortcode [jankx_my_account].', 'jankx'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="jankx_my_account_redirect_after_login"><?php esc_html_e('URL chuyển hướng sau đăng nhập', 'jankx'); ?></label>
                        </th>
                        <td>
                            <input type="url"
                                   id="jankx_my_account_redirect_after_login"
                                   name="jankx_my_account_redirect_after_login"
                                   value="<?php echo esc_attr(get_option('jankx_my_account_redirect_after_login', '')); ?>"
                                   class="regular-text"
                                   placeholder="<?php echo esc_url(home_url('/my-account')); ?>">
                            <p class="description"><?php esc_html_e('Để trống sẽ chuyển đến trang My Account.', 'jankx'); ?></p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Bật/tắt mục', 'jankx'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Hiển thị hồ sơ', 'jankx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="jankx_my_account_enable_profile" value="1"
                                    <?php checked(get_option('jankx_my_account_enable_profile', 1), 1); ?>>
                                <?php esc_html_e('Cho phép người dùng xem và chỉnh sửa hồ sơ.', 'jankx'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Hiển thị lịch sử đặt tour', 'jankx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="jankx_my_account_enable_bookings" value="1"
                                    <?php checked(get_option('jankx_my_account_enable_bookings', 1), 1); ?>>
                                <?php esc_html_e('Hiển thị danh sách các đơn đặt tour.', 'jankx'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Hiển thị số dư tín dụng', 'jankx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="jankx_my_account_enable_credits" value="1"
                                    <?php checked(get_option('jankx_my_account_enable_credits', 1), 1); ?>>
                                <?php esc_html_e('Hiển thị số dư và lịch sử giao dịch tín dụng.', 'jankx'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Hiển thị mã giảm giá', 'jankx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="jankx_my_account_enable_coupons" value="1"
                                    <?php checked(get_option('jankx_my_account_enable_coupons', 1), 1); ?>>
                                <?php esc_html_e('Hiển thị mã giảm giá đã được gán cho người dùng.', 'jankx'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Hiển thị cài đặt', 'jankx'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="jankx_my_account_enable_settings" value="1"
                                    <?php checked(get_option('jankx_my_account_enable_settings', 1), 1); ?>>
                                <?php esc_html_e('Cho phép người dùng thay đổi cài đặt thông báo.', 'jankx'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Hiển thị', 'jankx'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="jankx_my_account_avatar_size"><?php esc_html_e('Kích thước avatar (px)', 'jankx'); ?></label>
                        </th>
                        <td>
                            <input type="number"
                                   id="jankx_my_account_avatar_size"
                                   name="jankx_my_account_avatar_size"
                                   value="<?php echo esc_attr(get_option('jankx_my_account_avatar_size', 120)); ?>"
                                   min="48" max="512" step="8"
                                   class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="jankx_my_account_booking_per_page"><?php esc_html_e('Đơn đặt tour mỗi trang', 'jankx'); ?></label>
                        </th>
                        <td>
                            <input type="number"
                                   id="jankx_my_account_booking_per_page"
                                   name="jankx_my_account_booking_per_page"
                                   value="<?php echo esc_attr(get_option('jankx_my_account_booking_per_page', 10)); ?>"
                                   min="5" max="50" step="1"
                                   class="small-text">
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'jankx')); ?>
            </form>

            <div style="margin-top: 40px; padding: 20px; background: #fff; border: 1px solid #e2e4e7; border-radius: 8px; max-width: 700px;">
                <h2 style="margin-top: 0;"><?php esc_html_e('Preview', 'jankx'); ?></h2>
                <p>
                    <?php
                    $pageId = get_option('jankx_my_account_page_id', 0);
                    if ($pageId) :
                    ?>
                    <a href="<?php echo esc_url(get_permalink($pageId)); ?>"
                       target="_blank"
                       class="button button-primary">
                        <?php esc_html_e('Xem trang My Account', 'jankx'); ?>
                    </a>
                    <?php else : ?>
                    <span class="description"><?php esc_html_e('Vui lòng chọn trang My Account ở trên để xem trước.', 'jankx'); ?></span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <?php
    }
}
