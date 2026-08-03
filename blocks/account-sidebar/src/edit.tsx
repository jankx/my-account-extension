import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes }) {
    const blockProps = useBlockProps({
        className: 'jankx-account-sidebar is-editor-preview',
    });

    return (
        <div {...blockProps}>
            <div style={{
                padding: '20px',
                background: '#f8f9fa',
                borderRadius: '8px',
                border: '1px dashed #ddd'
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '16px' }}>
                    <div style={{
                        width: '60px',
                        height: '60px',
                        borderRadius: '50%',
                        background: '#e0e0e0',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center'
                    }}>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#999" strokeWidth="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <div style={{ fontWeight: 'bold', fontSize: '16px' }}>{__('User Name', 'jankx')}</div>
                        <div style={{ fontSize: '13px', color: '#666' }}>{__('Edit Profile', 'jankx')}</div>
                    </div>
                </div>
                <div style={{
                    padding: '12px',
                    background: '#CD7F3215',
                    border: '1px solid #CD7F3230',
                    borderRadius: '8px'
                }}>
                    <div style={{ fontWeight: 'bold', color: '#CD7F32' }}>{__('Bronze', 'jankx')}</div>
                    <div style={{ fontSize: '12px', color: '#666' }}>{__('Membership Badge', 'jankx')}</div>
                </div>
            </div>
        </div>
    );
}
