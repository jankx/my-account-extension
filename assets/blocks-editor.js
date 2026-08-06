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
  var select      = wp.data.select;
  var SSR         = wp.serverSideRender;
  var el          = wp.element.createElement;

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

      // jankx/sidebar-header — server-side render preview
      if (name === 'jankx/sidebar-header') {
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-server-rendered' });
          return el('div', blockProps,
            el(SSR, { block: 'jankx/sidebar-header', attributes: props.attributes })
          );
        };
        settings.save = function () { return null; };
      }

      // jankx/sidebar-nav — server-side render preview
      if (name === 'jankx/sidebar-nav') {
        settings.edit = function (props) {
          var blockProps = useBlockProps({ className: 'jankx-server-rendered' });
          return el('div', blockProps,
            el(SSR, { block: 'jankx/sidebar-nav', attributes: props.attributes })
          );
        };
        settings.save = function () { return null; };
      }

      return settings;
    }
  );

})();
