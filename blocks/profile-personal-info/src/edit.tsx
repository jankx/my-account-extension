import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes }) {
    const blockProps = useBlockProps({
        className: 'jankx-profile-personal-info is-editor-preview',
    });

    return (
        <div {...blockProps}>
            <ServerSideRender
                block="jankx/profile-personal-info"
                attributes={attributes}
            />
        </div>
    );
}
