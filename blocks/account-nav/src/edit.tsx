import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes }) {
    const blockProps = useBlockProps({
        className: 'jankx-account-nav is-editor-preview',
    });

    const tabs = [
        { label: __('Profile', 'jankx'), active: true },
        { label: __('Orders', 'jankx'), active: false },
        { label: __('Coupons', 'jankx'), active: false },
        { label: __('Credits', 'jankx'), active: false },
    ];

    return (
        <div {...blockProps}>
            <nav style={{ padding: '12px', background: '#f8f9fa', borderRadius: '8px', border: '1px dashed #ddd' }}>
                <ul style={{ listStyle: 'none', margin: 0, padding: 0 }}>
                    {tabs.map((tab, index) => (
                        <li key={index} style={{
                            padding: '10px 16px',
                            marginBottom: '4px',
                            borderRadius: '6px',
                            background: tab.active ? '#65A30D15' : 'transparent',
                            color: tab.active ? '#65A30D' : '#333',
                            fontWeight: tab.active ? '600' : '400',
                            cursor: 'pointer'
                        }}>
                            {tab.label}
                        </li>
                    ))}
                </ul>
            </nav>
        </div>
    );
}
