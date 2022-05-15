/**
 * WordPress dependencies.
 */
import { assign } from 'lodash';

import { __ } from '@wordpress/i18n';
 
import { hasBlockSupport } from '@wordpress/blocks';
 
import { PanelBody, SelectControl, TextControl, CheckboxControl } from '@wordpress/components';
 
import { createHigherOrderComponent } from '@wordpress/compose';
 
import { InspectorControls } from '@wordpress/block-editor';
 
import { Fragment, createRef } from '@wordpress/element';

import { addFilter } from '@wordpress/hooks';

import { select, subscribe } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import CSSEditor from './css-editor.js';

function addAttributes( settings ) {
    settings.attributes = assign( settings.attributes, {
        cugCSS: {
            type: 'object',
            default: ''
        },
    } );
    return settings;
}

addFilter(
    'blocks.registerBlockType',
    'cug/css/add-attributes',
    addAttributes
);


const withInspectorControls = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
        const { attributes, setAttributes } = props;

        function setText( value ) {
            setAttributes({
                cugCSS: value
            });            
        }

        function getText() {
            return attributes.cugCSS ?? '';
        }

        if ( props.isSelected ) {
            return (                
                <Fragment>
                    <BlockEdit { ...props } />
                    <InspectorControls>
                        <PanelBody
                            title={ __( 'CuG CSS', 'cug-css' ) }
                            initialOpen={ false }
                        >
                            <CSSEditor
                                setAttributes={ setAttributes }
                                attributes={ attributes }
                                editorId="cug-css-cm-editor"
                                setText={ setText }
                                getText={ getText }                         
                            />
                        </PanelBody>                        
                    </InspectorControls>                    
                </Fragment>
            );
        }

        return <BlockEdit { ...props } />;
    };
}, 'withInspectorControls' );


addFilter(
    'editor.BlockEdit',
    'ancug/css/with-inspector-controls',
    withInspectorControls
);


const withClassName = createHigherOrderComponent((BlockListBlock) => {
    return ( props ) => {        
        const { attributes, clientId  } = props;
        const { cugCSS } = attributes;

        let prefix = 'cug-css-';

        let doc = document;
        let iframes = document.getElementsByTagName("iFrame");
        

        for (let i = 0; i < iframes.length; i++) {
            if(iframes[i].getAttribute('name') == 'editor-canvas')
            {
                doc = iframes[i].contentWindow.document;
                break;
            }            
        };

        if( cugCSS ) {
            let elementStyle = doc.getElementById( prefix + clientId);
            if( ! elementStyle ) {
                elementStyle = doc.createElement( "style" );
                elementStyle.setAttribute( 'id', prefix + clientId );
                elementStyle.type = 'text/css';
                doc.body.appendChild( elementStyle );
            }
            elementStyle.textContent = cugCSS.replace( /\bTHIS\b/g, '.' + prefix + clientId);
        } else {
            let elementStyle = doc.getElementById( prefix + clientId );
            if( elementStyle ) {
                elementStyle.parentNode.removeChild( elementStyle );
            }            
        }

        if( cugCSS ) {
            return <BlockListBlock { ...props } className={ prefix + clientId } />;
        } else {
            return <BlockListBlock {...props} />
        }        
    };
}, 'withClientIdClassName' );

wp.hooks.addFilter( 
    'editor.BlockListBlock',
    'ancug/css/with-class-name',
    withClassName
);