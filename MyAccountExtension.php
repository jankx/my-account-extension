<?php
namespace Jankx\Extensions\MyAccount;

use Jankx\Extensions\AbstractExtension;

class MyAccountExtension extends AbstractExtension
{
    protected static $instance;

    protected static $subPages = [];

    public function init(): void
    {
        self::$instance = $this;

        // Register core sub-pages
        self::registerSubPage('profile', [
            'label' => 'Profile',
            'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            'priority' => 10,
            'callback' => [new \Jankx\Extensions\MyAccount\Shortcode\MyAccountShortcode(), 'renderProfileTab'],
        ]);
    }

    public static function get_instance(): ?self
    {
        return self::$instance;
    }

    /**
     * Register a sub-page for My Account
     */
    public static function registerSubPage(string $slug, array $args): void
    {
        $defaults = [
            'label' => '',
            'icon' => '',
            'priority' => 100,
            'extension' => null,
            'callback' => null,
            'show_in_nav' => true,
        ];

        self::$subPages[$slug] = wp_parse_args($args, $defaults);
    }

    /**
     * Get all registered sub-pages sorted by priority
     */
    public static function getSubPages(): array
    {
        $pages = self::$subPages;
        uasort($pages, function ($a, $b) {
            return ($a['priority'] ?? 100) <=> ($b['priority'] ?? 100);
        });
        return $pages;
    }

    /**
     * Get a specific sub-page
     */
    public static function getSubPage(string $slug): ?array
    {
        return self::$subPages[$slug] ?? null;
    }

    /**
     * Check if sub-page is valid
     */
    public static function isValidSubPage(string $slug): bool
    {
        return isset(self::$subPages[$slug]);
    }

    public function register_hooks(): void
    {
        $shortcode = new \Jankx\Extensions\MyAccount\Shortcode\MyAccountShortcode();
        $shortcode->register();

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('wp_ajax_jankx_update_profile', [$this, 'ajaxUpdateProfile']);
        add_action('wp_ajax_jankx_upload_avatar', [$this, 'ajaxUploadAvatar']);
        add_action('wp_ajax_jankx_change_password', [$this, 'ajaxChangePassword']);

        // Rewrite rules for sub-pages
        add_action('init', [$this, 'addRewriteRules']);
        add_action('init', [$this, 'addQueryVars']);

        // Intercept sub-page requests
        add_action('template_redirect', [$this, 'handleSubPage']);

        if (is_admin()) {
            $settingsPage = new \Jankx\Extensions\MyAccount\Admin\SettingsPage();
            $settingsPage->register();

            // Register Gutenberg blocks in admin (for block inserter)
            add_action('init', [$this, 'registerBlocks']);
        } else {
            // Frontend: only register blocks on My Account page
            add_action('template_redirect', [$this, 'maybeRegisterFrontendBlocks']);
        }

        // Fire action so other extensions can register their sub-pages
        // This must run after all extensions have registered their hooks
        add_action('init', function () {
            do_action('jankx/my_account/register_sub_pages');
        }, 99);

        // Inject My Account sub-pages into User Menu block dropdown
        add_filter('jankx/user_menu/items', [$this, 'addMyAccountMenuItems'], 20, 2);
    }

    /**
     * Register Gutenberg blocks for this extension
     */
    public function registerBlocks(): void
    {
        $blocksDir = __DIR__ . '/blocks';
        if (!is_dir($blocksDir)) {
            return;
        }

        $blockClasses = [
            'my-account' => \Jankx\Extensions\MyAccount\MyAccountBlock::class,
            'account-sidebar' => \Jankx\Extensions\MyAccount\AccountSidebarBlock::class,
            'account-nav' => \Jankx\Extensions\MyAccount\AccountNavBlock::class,
            'account-tab-profile' => \Jankx\Extensions\MyAccount\AccountTabProfileBlock::class,
        ];

        foreach ($blockClasses as $blockName => $blockClass) {
            $blockPath = $blocksDir . '/' . $blockName;
            if (!is_dir($blockPath)) {
                continue;
            }

            $block = new $blockClass($blockPath);
            $block->setBlockPath($blockPath);
            $block->boot();
            $block->register();
        }
    }

    /**
     * Check if current page is My Account page and register blocks if so
     */
    public function maybeRegisterFrontendBlocks(): void
    {
        if (!$this->isMyAccountPage()) {
            return;
        }

        $this->registerBlocks();
    }

    /**
     * Check if current page is My Account page or a sub-page
     */
    protected function isMyAccountPage(): bool
    {
        $pageId = get_option('jankx_my_account_page_id', 0);
        if (!$pageId) {
            return false;
        }

        // Check if current page is the My Account page
        if (is_page($pageId)) {
            return true;
        }

        // Check if current page is a sub-page (e.g., /my-account/profile/)
        $subPage = get_query_var('jankx_account_page');
        if (!empty($subPage) && self::isValidSubPage($subPage)) {
            return true;
        }

        // Check if we're on a page with the my-account shortcode
        global $post;
        if ($post && has_shortcode($post->post_content, 'jankx_my_account')) {
            return true;
        }

        return false;
    }

    /**
     * Add My Account sub-page links to User Menu dropdown
     */
    public function addMyAccountMenuItems(array $menuItems, $user): array
    {
        $subPages = self::getSubPages();
        if (empty($subPages)) {
            return $menuItems;
        }

        $pageId = get_option('jankx_my_account_page_id', 0);
        if (!$pageId) {
            return $menuItems;
        }

        $accountUrl = get_permalink($pageId);
        if (!$accountUrl) {
            return $menuItems;
        }

        // Insert sub-pages before logout
        $insertBefore = 'logout';
        $newItems = [];

        foreach ($menuItems as $id => $item) {
            if ($id === $insertBefore) {
                foreach ($subPages as $slug => $page) {
                    if (empty($page['show_in_nav'])) {
                        continue;
                    }
                    if (!empty($page['extension']) && !$this->isExtensionActiveForMenu($page['extension'])) {
                        continue;
                    }
                    $newItems['account_' . $slug] = [
                        'label' => $page['label'],
                        'url' => rtrim($accountUrl, '/') . '/' . $slug . '/',
                        'icon' => '',
                    ];
                }
            }
            $newItems[$id] = $item;
        }

        return $newItems;
    }

    /**
     * Check if extension is active (for menu rendering context)
     */
    protected function isExtensionActiveForMenu(string $extensionSlug): bool
    {
        try {
            $extensionManager = \Jankx\Facades\App::make('extension.manager');
            if ($extensionManager && method_exists($extensionManager, 'is_extension_active')) {
                return $extensionManager->is_extension_active($extensionSlug);
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * Add custom query vars
     */
    public function addQueryVars(): void
    {
        add_rewrite_tag('%jankx_account_page%', '([a-zA-Z0-9_-]+)');
    }

    /**
     * Add rewrite rules for My Account sub-pages
     */
    public function addRewriteRules(): void
    {
        $slug = $this->getPageSlug();
        if (empty($slug)) {
            return;
        }

        add_rewrite_rule(
            '^' . preg_quote($slug, '/') . '/([a-zA-Z0-9_-]+)/?$',
            'index.php?page=' . $slug . '&jankx_account_page=$matches[1]',
            'top'
        );
    }

    /**
     * Get the My Account page slug
     */
    protected function getPageSlug(): string
    {
        $slug = get_option('jankx_my_account_page_slug', '');
        if (empty($slug)) {
            $pageId = get_option('jankx_my_account_page_id', 0);
            if ($pageId) {
                $slug = get_page_uri($pageId);
            }
        }
        return $slug;
    }

    /**
     * Handle sub-page requests - load My Account page content
     */
    public function handleSubPage(): void
    {
        $subPage = get_query_var('jankx_account_page');
        if (empty($subPage)) {
            return;
        }

        if (!self::isValidSubPage($subPage)) {
            return;
        }

        $pageId = get_option('jankx_my_account_page_id', 0);
        if (!$pageId) {
            return;
        }

        // Load the My Account page
        $accountPage = get_post($pageId);
        if (!$accountPage) {
            return;
        }

        // Set the global post to the account page
        global $wp_query, $post;
        $wp_query->post = $accountPage;
        $wp_query->posts = [$accountPage];
        $wp_query->post_count = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_404 = false;
        $post = $accountPage;

        // Set the sub-page for the shortcode
        $_GET['tab'] = $subPage;

        // Prevent redirect loop
        remove_action('template_redirect', [$this, 'handleSubPage']);
    }

    /**
     * Install extension - create My Account page and flush rewrite rules
     */
    public function install(): bool
    {
        $this->createAccountPage();

        // Flush rewrite rules to register sub-page slugs
        flush_rewrite_rules();

        return parent::install();
    }

    /**
     * Create My Account page with shortcode
     */
    protected function createAccountPage(): void
    {
        $pageId = get_option('jankx_my_account_page_id', 0);
        if ($pageId && get_post_status($pageId) === 'publish') {
            return;
        }

        $pageData = [
            'post_title' => __('My Account', 'jankx'),
            'post_content' => '[jankx_my_account]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => get_current_user_id(),
        ];

        $pageId = wp_insert_post($pageData);

        if ($pageId && !is_wp_error($pageId)) {
            update_option('jankx_my_account_page_id', $pageId);
            update_option('jankx_my_account_page_slug', get_page_uri($pageId));
        }
    }

    public function enqueueAssets(): void
    {
        if (!is_user_logged_in()) {
            return;
        }

        $pageId = get_option('jankx_my_account_page_id', 0);
        if (!$pageId && !is_page($pageId)) {
            global $post;
            if (!$post || !has_shortcode($post->post_content, 'jankx_my_account')) {
                return;
            }
        }

        wp_enqueue_style(
            'jankx-my-account',
            $this->get_extension_url() . '/assets/frontend.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'jankx-my-account',
            $this->get_extension_url() . '/assets/frontend.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('jankx-my-account', 'jankxMyAccount', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('jankx_my_account_nonce'),
            'i18n' => [
                'saving' => 'Saving...',
                'saved' => 'Saved successfully!',
                'error' => 'An error occurred. Please try again.',
                'confirmDelete' => 'Are you sure you want to delete?',
                'uploading' => 'Uploading...',
            ],
        ]);
    }

    public function ajaxUpdateProfile(): void
    {
        check_ajax_referer('jankx_my_account_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please login.']);
        }

        $userId = get_current_user_id();
        $displayName = sanitize_text_field($_POST['display_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');

        if (empty($displayName)) {
            wp_send_json_error(['message' => 'Name cannot be empty.']);
        }

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(['message' => 'Invalid email address.']);
        }

        if (email_exists($email) && $email !== get_userdata($userId)->user_email) {
            wp_send_json_error(['message' => 'Email is already in use by another account.']);
        }

        $userData = [
            'ID' => $userId,
            'display_name' => $displayName,
            'user_email' => $email,
        ];

        $result = wp_update_user($userData);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => 'Error updating profile.']);
        }

        update_user_meta($userId, 'phone', $phone);

        wp_send_json_success(['message' => 'Profile updated successfully!']);
    }

    public function ajaxUploadAvatar(): void
    {
        check_ajax_referer('jankx_my_account_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please login.']);
        }

        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => 'Please select an image file.']);
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowedTypes)) {
            wp_send_json_error(['message' => 'Only JPG, PNG, GIF or WebP files are accepted.']);
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(['message' => 'File size cannot exceed 5MB.']);
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $attachId = media_handle_upload('avatar', 0);

        if (is_wp_error($attachId)) {
            wp_send_json_error(['message' => 'Failed to upload image.']);
        }

        $userId = get_current_user_id();
        update_user_meta($userId, 'jankx_avatar_id', $attachId);
        update_user_meta($userId, 'jankx_avatar_url', wp_get_attachment_url($attachId));

        wp_send_json_success([
            'message' => 'Avatar updated successfully!',
            'url' => wp_get_attachment_image_url($attachId, 'thumbnail'),
        ]);
    }

    public function ajaxChangePassword(): void
    {
        check_ajax_referer('jankx_my_account_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Please login.']);
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) {
            wp_send_json_error(['message' => 'Please enter your current password.']);
        }

        $user = wp_get_current_user();
        if (!wp_check_password($currentPassword, $user->data->user_pass, $user->ID)) {
            wp_send_json_error(['message' => 'Current password is incorrect.']);
        }

        if (strlen($newPassword) < 8) {
            wp_send_json_error(['message' => 'New password must be at least 8 characters.']);
        }

        if ($newPassword !== $confirmPassword) {
            wp_send_json_error(['message' => 'New passwords do not match.']);
        }

        wp_set_password($newPassword, $user->ID);

        wp_send_json_success(['message' => 'Password changed successfully!']);
    }
}
