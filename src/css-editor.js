/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { CodeEditor } from '@wordpress/components';
import { Fragment, useEffect } from '@wordpress/element';


export default function CSSEditor ( props ) { 
    const { setText, getText, editorId } = props;

    useEffect( () => {

        let editor = wp.CodeMirror( document.getElementById(editorId), {
            value: getText(),
            mode: "text/css",
            styleActiveLine: true,
            styleActiveSelected: true,            
            autoCloseBrackets: true,
            continueComments: true,
            lineNumbers: true,
            lineWrapping: true,
            matchBrackets: true,
        });

        editor.on( 'change', ( editor ) => {
            let value = editor.getValue().replace(/^\s+|\s+$/g, '');
            setText(value);
        });
    }, []);

    return (
        <Fragment>
            <p>{ __( 'Add your custom CSS.', 'cug-css' ) }</p>
            <div id={ editorId } className={ editorId }/>
        </Fragment>
    );


};