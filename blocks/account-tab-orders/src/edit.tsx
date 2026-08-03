import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
    const blockProps = useBlockProps({
        className: 'jankx-account-tab-orders is-editor-preview',
    });

    return (
        <div {...blockProps}>
            <div style={{ padding: '20px', background: '#f8f9fa', borderRadius: '8px', border: '1px dashed #ddd' }}>
                <h3 style={{ margin: '0 0 16px', fontSize: '16px' }}>{__('Orders', 'jankx')}</h3>
                <div style={{ textAlign: 'center', padding: '40px 20px', color: '#999' }}>
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" strokeWidth="1.5">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <p style={{ marginTop: '12px' }}>{__('Your orders will appear here', 'jankx')}</p>
                </div>
            </div>
        </div>
    );
}
