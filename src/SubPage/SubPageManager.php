<?php

namespace Jankx\Extensions\MyAccount\SubPage;

class SubPageManager
{
    const POST_TYPE = 'jankx_account_page';
    const SLUG_META = '_jankx_account_page_slug';
    const DEFAULT_META = '_jankx_account_page_default';

    private static $instance;

    private $slugPosts = [];
    private $loaded = false;

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function registerPostType(): void
    {
        if (post_type_exists(self::POST_TYPE)) {
            return;
        }

        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name' => __('My Account Pages', 'jankx'),
                'singular_name' => __('My Account Page', 'jankx'),
                'edit_item' => __('Edit Account Page', 'jankx'),
                'edit_items' => __('Edit Account Pages', 'jankx'),
            ],
            'public' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_menu' => 'jankx-theme-options',
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'rest_base' => 'account-pages',
            'query_var' => false,
            'rewrite' => false,
            'has_archive' => false,
            'hierarchical' => false,
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'do_not_allow',
            ],
            'map_meta_cap' => true,
            'supports' => ['title', 'editor'],
            'delete_with_user' => false,
        ]);
    }

    public function sync(array $subPages): void
    {
        foreach ($subPages as $slug => $page) {
            if (empty($page['content'])) {
                continue;
            }

            $postId = $this->getPostIdBySlug($slug);

            if (!$postId) {
                $this->createPost($slug, $page);
                continue;
            }

            $this->maybeUpdatePost($postId, $slug, $page['content']);
        }
    }

    protected function createPost(string $slug, array $page): int
    {
        $postId = wp_insert_post([
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $page['label'] ?: $slug,
            'post_content' => $page['content'],
        ], true);

        if (is_wp_error($postId)) {
            return 0;
        }

        update_post_meta($postId, self::SLUG_META, $slug);
        update_post_meta($postId, self::DEFAULT_META, $page['content']);

        $this->slugPosts[$slug] = (int) $postId;

        return (int) $postId;
    }

    protected function maybeUpdatePost(int $postId, string $slug, string $content): void
    {
        $post = get_post($postId);
        if (!$post) {
            return;
        }

        $appliedDefault = (string) get_post_meta($postId, self::DEFAULT_META, true);

        if ($appliedDefault === '') {
            return;
        }

        if ($appliedDefault === $content) {
            return;
        }

        if ($post->post_content !== $appliedDefault) {
            return;
        }

        wp_update_post([
            'ID' => $postId,
            'post_content' => $content,
        ]);

        update_post_meta($postId, self::DEFAULT_META, $content);
        $this->slugPosts[$slug] = $postId;
    }

    public function getPostIdBySlug(string $slug): int
    {
        $this->loadSlugPosts();
        return isset($this->slugPosts[$slug]) ? (int) $this->slugPosts[$slug] : 0;
    }

    protected function loadSlugPosts(): void
    {
        if ($this->loaded) {
            return;
        }
        $this->loaded = true;

        $existing = get_posts([
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ]);

        foreach ($existing as $postId) {
            $slug = get_post_meta($postId, self::SLUG_META, true);
            if ($slug) {
                $this->slugPosts[$slug] = (int) $postId;
            }
        }
    }

    public function getEditUrl(string $slug): string
    {
        $postId = $this->getPostIdBySlug($slug);
        if (!$postId) {
            return '';
        }

        return get_edit_post_link($postId, '');
    }

    public function renderPostContent(int $postId): string
    {
        $post = get_post($postId);
        if (!$post || $post->post_status !== 'publish') {
            return '';
        }

        $content = $this->hydrateTabBlocks($post->post_content);

        return apply_filters('the_content', $content);
    }

    protected function hydrateTabBlocks(string $content): string
    {
        return preg_replace_callback(
            '/<!--\s+wp:(jankx\/account-tab-[a-z0-9-]+)\b.*?\/-->/s',
            function ($matches) {
                $blockName = $matches[1];

                if (\WP_Block_Type_Registry::get_instance()->is_registered($blockName)) {
                    return $matches[0];
                }

                return $this->renderTabBlockManually($blockName);
            },
            $content
        );
    }

    protected function renderTabBlockManually(string $blockName): string
    {
        switch ($blockName) {
            case 'jankx/account-tab-orders':
                if (class_exists(\Jankx\Extensions\Ecommerce\Blocks\AccountTabOrdersBlock::class)) {
                    return (new \Jankx\Extensions\Ecommerce\Blocks\AccountTabOrdersBlock())->render([]);
                }
                if (class_exists(\Jankx\Extensions\Travel\Blocks\AccountTabOrdersBlock::class)) {
                    return (new \Jankx\Extensions\Travel\Blocks\AccountTabOrdersBlock())->render([]);
                }
                return '';

            case 'jankx/account-tab-coupons':
                if (class_exists(\Jankx\Extensions\CouponSystem\Blocks\AccountTabCouponsBlock::class)) {
                    return (new \Jankx\Extensions\CouponSystem\Blocks\AccountTabCouponsBlock())->render([]);
                }
                return '';

            case 'jankx/account-tab-credits':
                if (class_exists(\Jankx\Extensions\UserCredits\Blocks\AccountTabCreditsBlock::class)) {
                    return (new \Jankx\Extensions\UserCredits\Blocks\AccountTabCreditsBlock())->render([]);
                }
                return '';

            case 'jankx/account-tab-membership':
                if (class_exists(\Jankx\Extensions\MembershipLevels\Blocks\AccountTabMembershipBlock::class)) {
                    return (new \Jankx\Extensions\MembershipLevels\Blocks\AccountTabMembershipBlock())->render([]);
                }
                return '';

            case 'jankx/account-tab-profile':
                if (class_exists(\Jankx\Extensions\MyAccount\AccountTabProfileBlock::class)) {
                    return (new \Jankx\Extensions\MyAccount\AccountTabProfileBlock())->render([]);
                }
                return '';
        }

        return '';
    }
}