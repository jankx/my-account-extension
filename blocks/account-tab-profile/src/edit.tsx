import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
    const blockProps = useBlockProps({
        className: 'jankx-account-tab-profile is-editor-preview',
    });

    return (
        <div {...blockProps}>
            <div style={{ padding: '20px', background: '#f8f9fa', borderRadius: '8px', border: '1px dashed #ddd' }}>
                <h3 style={{ margin: '0 0 16px', fontSize: '16px' }}>{__('Profile', 'jankx')}</h3>
                <div style={{ marginBottom: '12px' }}>
                    <label style={{ display: 'block', marginBottom: '4px', fontSize: '13px', fontWeight: '500' }}>{__('Full Name', 'jankx')}</label>
                    <div style={{ padding: '10px', background: '#fff', border: '1px solid #ddd', borderRadius: '4px' }}>{__('User Name', 'jankx')}</div>
                </div>
                <div style={{ marginBottom: '12px' }}>
                    <label style={{ display: 'block', marginBottom: '4px', fontSize: '13px', fontWeight: '500' }}>{__('Email', 'jankx')}</label>
                    <div style={{ padding: '10px', background: '#fff', border: '1px solid #ddd', borderRadius: '4px' }}>user@example.com</div>
                </div>
                <div style={{ padding: '8px 16px', background: '#65A30D', color: '#fff', borderRadius: '4px', display: 'inline-block', fontSize: '13px' }}>
                    {__('Save Changes', 'jankx')}
                </div>
            </div>
        </div>
    );
}
