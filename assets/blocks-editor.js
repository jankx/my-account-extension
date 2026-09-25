/**
 * My Account Blocks - Editor integration
 *
 * Registers all My Account blocks client-side and injects their
 * edit/save functions. Blocks without their own editorScript
 * (no build step) are registered here using metadata passed from
 * PHP via wp_localize_script. The blocks.registerBlockType filter
 * then injects the correct edit functions so inner blocks are
 * preserved correctly when saving.
 */
(function () {
  var registerBlockType = wp.blocks.registerBlockType;
  var addFilter   = wp.hooks.addFilter;
  var InnerBlocks = wp.blockEditor.InnerBlocks;
  var useBlockProps = wp.blockEditor.useBlockProps;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var Fragment    = wp.element.Fragment;
  var select      = wp.data.select;
  var SSR         = wp.serverSideRender;
  var el          = wp.element.createElement;
  var PanelBody   = wp.components.PanelBody;
  var ColorPalette = wp.components.ColorPalette;
  var RangeControl = wp.components.RangeControl;
  var __          = wp.i18n.__;

  var PLACEHOLDER_ICON = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>';

  // Register blocks that lack editorScript in block.json.
  // Metadata is passed from PHP via wp_localize_script.
  var blockMetadata = window.jankxMyAccountBlockMetadata || {};
  Object.keys(blockMetadata).forEach(function (blockName) {
    // Skip if already registered client-side (has editorScript)
    if (select('core/blocks').getBlockType(blockName)) {
      return;
    }
    registerBlockType(blockName, blockMetadata[blockName]);
  });

  var accountMenu = window.jankxMyAccountMenu || [];

  function menuItemTemplate() {
    return accountMenu.map(function (item) {
      return [
        'jankx/account-menu-item',
        { slug: item.slug, label: item.label, icon: item.icon },
        [
          ['jankx/account-menu-icon', {}],
          ['jankx/account-menu-text', {}]
        ]
      ];
    });
  }

  // ──────────────────────────────────────────────────────────
  // Inject edit functions via filter — runs at registration time,
  // before Gutenberg parses the saved post content.
  // ──────────────────────────────────────────────────────────
  addFilter(
    'blocks.registerBlockType',
    'jankx/my-account-editor-inject',
    function (settings, name) {

      // jankx/my-account — container with InnerBlocks
      if (name === 'jankx/my-account') {
        settings.edit = function () {
          var blockProps = useBlockProps({ className: 'jankx-my-account-editor' });
          return el('div', blockProps,
            el('div', { className: 'jankx-account-layout' },
              el(InnerBlocks, {
                templateLock: false,
                orientation: 'vertical'
              })
            )
          );
        };
        settings.save = function (_props) {
          return el(InnerBlocks.Content);
        };
      }

      // jankx/account-sidebar — container with InnerBlocks
      if (name === 'jankx/account-sidebar') {
        // Override parent → ancestor so block can be placed inside
        // my-account at any nesting level (e.g. inside core/columns).
        delete settings.parent;
        settings.ancestor = ['jankx/my-account'];
        settings.edit = function () {
          var blockProps = useBlockProps({ className: 'jankx-account-sidebar-editor' });
          return el('div', blockProps,
            el(InnerBlocks, {
              template: [
                ['jankx/sidebar-header', {}],
                ['jankx/account-level-summary', {}],
                ['jankx/sidebar-nav', {}]
              ],
              templateLock: false,
              orientation: 'vertical'
            })
          );
        };
        settings.save = function (_props) {
          return el(InnerBlocks.Content);
        };
      }

      // jankx/account-content — server-side render preview
      if (name === 'jankx/account-content') {
        delete settings.parent;
        settings.ancestor = ['jankx/my-account'];
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-account-content-editor' });
          return el('div', blockProps,
            el(SSR, { block: 'jankx/account-content', attributes: props.attributes })
          );
        };
        settings.save = function () { return null; };
      }

      // jankx/content-header — server-side render preview
      if (name === 'jankx/content-header') {
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-server-rendered' });
          return el('div', blockProps,
            el(SSR, { block: 'jankx/content-header', attributes: props.attributes })
          );
        };
        settings.save = function () { return null; };
      }

      // jankx/sidebar-header — group-like wrapper with overlay control
      if (name === 'jankx/sidebar-header') {
        settings.edit = function (props) {
          var attributes = props.attributes;
          var setAttributes = props.setAttributes;
          var blockProps = useBlockProps({ className: 'jankx-server-rendered' });

          return el(Fragment, null,
            el(InspectorControls, null,
              el(PanelBody, { title: __('Overlay', 'jankx'), initialOpen: false },
                el('div', { className: 'jankx-overlay-control' },
                  el(ColorPalette, {
                    label: __('Overlay color', 'jankx'),
                    value: attributes.overlayColor || '',
                    onChange: function (value) {
                      setAttributes({ overlayColor: value || '' });
                    },
                    disableCustomColors: false
                  })
                ),
                el(RangeControl, {
                  label: __('Overlay opacity', 'jankx'),
                  value: attributes.overlayOpacity,
                  min: 0,
                  max: 100,
                  step: 5,
                  onChange: function (value) {
                    setAttributes({ overlayOpacity: value === undefined ? 50 : value });
                  },
                  __nextHasNoMarginBottom: true
                })
              )
            ),
            el('div', blockProps,
              el(SSR, { block: 'jankx/sidebar-header', attributes: attributes })
            )
          );
        };
        settings.save = function () { return null; };
      }

      // jankx/account-level-summary — server-side render preview
      if (name === 'jankx/account-level-summary') {
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-server-rendered' });
          return el('div', blockProps,
            el(SSR, { block: 'jankx/account-level-summary', attributes: props.attributes })
          );
        };
        settings.save = function () { return null; };
      }

      // jankx/sidebar-nav — InnerBlocks container of account menu items
      if (name === 'jankx/sidebar-nav') {
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-sidebar-nav-editor' });
          return el('nav', blockProps,
            el(InnerBlocks, {
              template: menuItemTemplate(),
              allowedBlocks: ['jankx/account-menu-item'],
              templateLock: false,
              orientation: 'vertical',
              renderAppender: InnerBlocks.ButtonBlockAppender
            })
          );
        };
        settings.save = function () {
          return el(InnerBlocks.Content);
        };
      }

      // jankx/account-menu-item — icon + label template, wrapped in the nav link
      if (name === 'jankx/account-menu-item') {
        settings.edit = function (props) {
          var attributes = props.attributes;
          var blockProps = useBlockProps({ className: 'jankx-nav-item' });
          var menu = accountMenu.filter(function (item) {
            return item.slug === attributes.slug;
          })[0];

          return el('div', blockProps,
            el('a', {
              className: 'jankx-nav-link',
              href: (menu && menu.url) || '#'
            },
              el(InnerBlocks, {
                template: [
                  ['jankx/account-menu-icon', {}],
                  ['jankx/account-menu-text', {}]
                ],
                templateLock: 'all',
                orientation: 'horizontal'
              })
            )
          );
        };
        settings.save = function () {
          return el(InnerBlocks.Content);
        };
      }

      // jankx/account-menu-icon — icon resolved from block context
      if (name === 'jankx/account-menu-icon') {
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-nav-icon' });
          var icon = (props.context && props.context['jankx/menuIcon']) || PLACEHOLDER_ICON;
          return el('span', Object.assign({}, blockProps, {
            dangerouslySetInnerHTML: { __html: icon }
          }));
        };
        settings.save = function () { return null; };
      }

      // jankx/account-menu-text — label resolved from block context
      if (name === 'jankx/account-menu-text') {
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-nav-label' });
          var label = (props.context && props.context['jankx/menuLabel']) || __('Menu item', 'jankx');
          return el('span', blockProps, label);
        };
        settings.save = function () { return null; };
      }

      return settings;
    }
  );

})();
