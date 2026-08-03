import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
    const blockProps = useBlockProps({
        className: 'jankx-my-account-block is-editor-preview',
    });

    return (
        <div {...blockProps}>
            <div className="jankx-account-preview-header" style={{
                padding: '20px',
                background: '#f8f9fa',
                borderRadius: '8px',
                marginBottom: '16px',
                border: '1px dashed #ddd'
            }}>
                <h3 style={{ margin: 0, fontSize: '16px' }}>
                    {__('My Account', 'jankx')}
                </h3>
                <p style={{ margin: '8px 0 0', fontSize: '13px', color: '#666' }}>
                    {__('Container block for the My Account page. Add sidebar and tab blocks inside.', 'jankx')}
                </p>
            </div>
            <InnerBlocks
                template={[
                    ['jankx/account-sidebar', {}],
                    ['jankx/account-nav', {}],
                    ['jankx/account-tab-profile', {}],
                ]}
                allowedBlocks={[
                    'jankx/account-sidebar',
                    'jankx/account-nav',
                    'jankx/account-tab-profile',
                    'jankx/account-tab-orders',
                    'jankx/account-tab-coupons',
                    'jankx/account-tab-credits',
                ]}
            />
        </div>
    );
}
