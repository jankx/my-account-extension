/**
 * My Account Blocks - Client-side registration with ServerSideRender
 * Editor preview matches frontend 100%.
 */
(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var ServerSideRender = wp.serverSideRender;
  var __ = wp.i18n.__;
  var el = wp.element.createElement;

  var blocks = [
    {
      name: "jankx/my-account",
      title: __("My Account", "jankx"),
      icon: "admin-users",
      category: "jankx",
      attrs: {
        showSidebar: { type: "boolean", default: true },
        layout: { type: "string", default: "sidebar-content", enum: ["sidebar-content", "content-sidebar", "stacked"] }
      },
      supports: { html: false, align: ["wide", "full"], spacing: { margin: true, padding: true, blockGap: true } },
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-my-account-editor" });
        return el("div", blockProps,
          el(ServerSideRender, {
            block: "jankx/my-account",
            attributes: props.attributes
          })
        );
      }
    },
    {
      name: "jankx/account-sidebar",
      title: __("Account Sidebar", "jankx"),
      icon: "admin-users",
      category: "jankx",
      parent: ["jankx/my-account"],
      attrs: {
        showAvatar: { type: "boolean", default: true },
        showName: { type: "boolean", default: true },
        showMembershipBadge: { type: "boolean", default: true }
      },
      supports: { html: false },
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-account-sidebar-editor" });
        return el("div", blockProps,
          el(ServerSideRender, {
            block: "jankx/account-sidebar",
            attributes: props.attributes
          })
        );
      }
    },
    {
      name: "jankx/account-nav",
      title: __("Account Navigation", "jankx"),
      icon: "menu-alt3",
      category: "jankx",
      parent: ["jankx/my-account"],
      attrs: {
        orientation: { type: "string", default: "vertical" }
      },
      supports: { html: false },
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-account-nav-editor" });
        return el("div", blockProps,
          el(ServerSideRender, {
            block: "jankx/account-nav",
            attributes: props.attributes
          })
        );
      }
    },
    {
      name: "jankx/account-tab-profile",
      title: __("Profile Tab", "jankx"),
      icon: "id",
      category: "jankx",
      parent: ["jankx/my-account"],
      attrs: {},
      supports: { html: false },
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-account-tab-editor" });
        return el("div", blockProps,
          el(ServerSideRender, {
            block: "jankx/account-tab-profile",
            attributes: props.attributes
          })
        );
      }
    },
    {
      name: "jankx/account-tab-orders",
      title: __("Orders Tab", "jankx"),
      icon: "list-view",
      category: "jankx",
      parent: ["jankx/my-account"],
      attrs: {},
      supports: { html: false },
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-account-tab-editor" });
        return el("div", blockProps,
          el(ServerSideRender, {
            block: "jankx/account-tab-orders",
            attributes: props.attributes
          })
        );
      }
    },
    {
      name: "jankx/account-tab-coupons",
      title: __("Coupons Tab", "jankx"),
      icon: "tickets",
      category: "jankx",
      parent: ["jankx/my-account"],
      attrs: {},
      supports: { html: false },
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-account-tab-editor" });
        return el("div", blockProps,
          el(ServerSideRender, {
            block: "jankx/account-tab-coupons",
            attributes: props.attributes
          })
        );
      }
    },
    {
      name: "jankx/account-tab-credits",
      title: __("Credits Tab", "jankx"),
      icon: "money-alt",
      category: "jankx",
      parent: ["jankx/my-account"],
      attrs: {},
      supports: { html: false },
      edit: function (props) {
        var blockProps = useBlockProps({ className: "jankx-account-tab-editor" });
        return el("div", blockProps,
          el(ServerSideRender, {
            block: "jankx/account-tab-credits",
            attributes: props.attributes
          })
        );
      }
    }
  ];

  blocks.forEach(function (b) {
    registerBlockType(b.name, {
      apiVersion: 3,
      title: b.title,
      icon: b.icon,
      category: b.category,
      parent: b.parent,
      attributes: b.attrs,
      supports: b.supports,
      edit: b.edit,
      save: function () { return null; }
    });
  });
})();
