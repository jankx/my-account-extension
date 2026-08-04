import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes }) {
    const blockProps = useBlockProps({
        className: 'jankx-account-nav is-editor-preview',
    });

    return (
        <div {...blockProps}>
            <ServerSideRender
                block="jankx/account-nav"
                attributes={attributes}
            />
        </div>
    );
}
