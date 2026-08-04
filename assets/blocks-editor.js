/**
 * My Account Blocks - Client-side registration
 * - Container blocks use InnerBlocks (allows child blocks)
 * - Content blocks use ServerSideRender (live preview)
 */
(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var InnerBlocks = wp.blockEditor.InnerBlocks;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var ServerSideRender = wp.serverSideRender;
  var __ = wp.i18n.__;
  var el = wp.element.createElement;

  // ──────────────────────────────────────
  // CONTAINER BLOCKS — use InnerBlocks
  // ──────────────────────────────────────

  // My Account — accepts ALL blocks
  registerBlockType("jankx/my-account", {
    apiVersion: 3,
    title: __("My Account", "jankx"),
    icon: "admin-users",
    category: "jankx",
    attributes: {
      layout: { type: "string", default: "sidebar-content", enum: ["sidebar-content", "content-sidebar", "stacked"] }
    },
    supports: { html: false, align: ["wide", "full"], spacing: { margin: true, padding: true, blockGap: true } },
    edit: function (props) {
      var blockProps = useBlockProps({ className: "jankx-my-account-editor" });
      return el("div", blockProps,
        el("div", { className: "jankx-account-layout" },
          el(InnerBlocks, {
            templateLock: false,
            orientation: "vertical"
          })
        )
      );
    },
    save: function () { return null; }
  });

  // Account Sidebar — accepts sidebar-header + sidebar-nav
  registerBlockType("jankx/account-sidebar", {
    apiVersion: 3,
    title: __("Account Sidebar", "jankx"),
    icon: "layout",
    category: "jankx",
    parent: ["jankx/my-account"],
    attributes: {},
    supports: { html: false, spacing: { padding: true, margin: true } },
    edit: function (props) {
      var blockProps = useBlockProps({ className: "jankx-account-sidebar-editor" });
      return el("div", blockProps,
        el("div", { className: "jankx-sidebar-header-preview" },
          el("p", null, __("Sidebar Header", "jankx"))
        ),
        el(InnerBlocks, {
          allowedBlocks: ["jankx/sidebar-header", "jankx/sidebar-nav"],
          template: [
            ["jankx/sidebar-header", {}],
            ["jankx/sidebar-nav", {}]
          ],
          templateLock: false,
          orientation: "vertical"
        })
      );
    },
    save: function () { return null; }
  });

  // Account Content — accepts content-header + tab blocks
  registerBlockType("jankx/account-content", {
    apiVersion: 3,
    title: __("Account Content", "jankx"),
    icon: "editor-expand",
    category: "jankx",
    parent: ["jankx/my-account"],
    attributes: {},
    supports: { html: false, spacing: { padding: true, margin: true } },
    edit: function (props) {
      var blockProps = useBlockProps({ className: "jankx-account-content-editor" });
      return el("div", blockProps,
        el(InnerBlocks, {
          allowedBlocks: [
            "jankx/content-header",
            "jankx/account-tab-profile",
            "jankx/account-tab-orders",
            "jankx/account-tab-coupons",
            "jankx/account-tab-credits"
          ],
          template: [
            ["jankx/content-header", {}],
            ["jankx/account-tab-profile", {}]
          ],
          templateLock: false,
          orientation: "vertical"
        })
      );
    },
    save: function () { return null; }
  });

  // ──────────────────────────────────────
  // CONTENT BLOCKS — use ServerSideRender
  // ──────────────────────────────────────

  var contentBlocks = [
    {
      name: "jankx/sidebar-header",
      title: __("Sidebar Header", "jankx"),
      icon: "admin-users",
      category: "jankx",
      parent: ["jankx/account-sidebar"],
      attrs: {
        showAvatar: { type: "boolean", default: true },
        showName: { type: "boolean", default: true },
        showMembershipBadge: { type: "boolean", default: true },
        showEditLink: { type: "boolean", default: true }
      },
      supports: { html: false }
    },
    {
      name: "jankx/sidebar-nav",
      title: __("Sidebar Navigation", "jankx"),
      icon: "menu",
      category: "jankx",
      parent: ["jankx/account-sidebar"],
      attrs: {},
      supports: { html: false }
    },
    {
      name: "jankx/content-header",
      title: __("Content Header", "jankx"),
      icon: "text",
      category: "jankx",
      parent: ["jankx/account-content"],
      attrs: {},
      supports: { html: false }
    },
    {
      name: "jankx/account-tab-profile",
      title: __("Profile Tab", "jankx"),
      icon: "id",
      category: "jankx",
      parent: ["jankx/account-content"],
      attrs: {},
      supports: { html: false }
    },
    {
      name: "jankx/account-tab-orders",
      title: __("Orders Tab", "jankx"),
      icon: "list-view",
      category: "jankx",
      parent: ["jankx/account-content"],
      attrs: {},
      supports: { html: false }
    },
    {
      name: "jankx/account-tab-coupons",
      title: __("Coupons Tab", "jankx"),
      icon: "tickets",
      category: "jankx",
      parent: ["jankx/account-content"],
      attrs: {},
      supports: { html: false }
    },
    {
      name: "jankx/account-tab-credits",
      title: __("Credits Tab", "jankx"),
      icon: "money-alt",
      category: "jankx",
      parent: ["jankx/account-content"],
      attrs: {},
      supports: { html: false }
    }
  ];

  contentBlocks.forEach(function (b) {
    registerBlockType(b.name, {
      apiVersion: 3,
      title: b.title,
      icon: b.icon,
      category: b.category,
      parent: b.parent,
      attributes: b.attrs,
      supports: b.supports,
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-server-rendered" });
        return el("div", blockProps,
          el(ServerSideRender, { block: b.name, attributes: props.attributes })
        );
      },
      save: function () { return null; }
    });
  });
})();
