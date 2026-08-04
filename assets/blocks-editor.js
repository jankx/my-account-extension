/**
 * My Account Blocks - Client-side registration for the block editor.
 */
(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var __ = wp.i18n.__;

  function createEdit(title, icon) {
    return function () {
      return wp.element.createElement(
        "div",
        { className: "jankx-block-placeholder", style: { padding: "20px", textAlign: "center", background: "#f9f9f9", borderRadius: "8px", border: "2px dashed #ddd", margin: "16px 0" } },
        wp.element.createElement("div", { style: { fontSize: "32px", marginBottom: "8px" } }, icon),
        wp.element.createElement("p", { style: { margin: 0, fontWeight: 600, color: "#333" } }, title),
        wp.element.createElement("p", { style: { margin: "4px 0 0", fontSize: "12px", color: "#999" } }, "Server-rendered on frontend")
      );
    };
  }

  var blocks = [
    { name: "jankx/my-account", title: __("My Account", "jankx"), icon: "admin-users", attrs: { showSidebar: { type: "boolean", default: true }, layout: { type: "string", default: "sidebar-content" } }, supports: { html: false, align: ["wide", "full"] }, label: __("My Account Container", "jankx"), emoji: "👤" },
    { name: "jankx/account-sidebar", title: __("Account Sidebar", "jankx"), icon: "admin-users", parent: ["jankx/my-account"], attrs: { showAvatar: { type: "boolean", default: true }, showName: { type: "boolean", default: true }, showMembershipBadge: { type: "boolean", default: true }, showEditLink: { type: "boolean", default: true } }, supports: { html: false }, label: __("Account Sidebar", "jankx"), emoji: "📋" },
    { name: "jankx/account-nav", title: __("Account Navigation", "jankx"), icon: "menu-alt3", parent: ["jankx/my-account"], attrs: { orientation: { type: "string", default: "vertical" } }, supports: { html: false }, label: __("Account Navigation", "jankx"), emoji: "🧭" },
    { name: "jankx/account-tab-profile", title: __("Profile Tab", "jankx"), icon: "id", parent: ["jankx/my-account"], supports: { html: false }, label: __("Profile Tab", "jankx"), emoji: "📝" },
    { name: "jankx/account-tab-orders", title: __("Orders Tab", "jankx"), icon: "list-view", parent: ["jankx/my-account"], supports: { html: false }, label: __("Orders Tab", "jankx"), emoji: "📦" },
    { name: "jankx/account-tab-coupons", title: __("Coupons Tab", "jankx"), icon: "tickets", parent: ["jankx/my-account"], supports: { html: false }, label: __("Coupons Tab", "jankx"), emoji: "🎟️" },
    { name: "jankx/account-tab-credits", title: __("Credits Tab", "jankx"), icon: "money-alt", parent: ["jankx/my-account"], supports: { html: false }, label: __("Credits Tab", "jankx"), emoji: "💰" }
  ];

  blocks.forEach(function (b) {
    registerBlockType(b.name, {
      apiVersion: 3,
      title: b.title,
      icon: b.icon,
      category: "jankx",
      parent: b.parent,
      attributes: b.attrs || {},
      supports: b.supports,
      edit: createEdit(b.label, b.emoji),
      save: function () { return null; }
    });
  });
})();
