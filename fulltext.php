<?php

/*
Addon Name: Full Text Importer
Description: Takes full text from original article url.
Author: sandro@weare.de.com
Author URI: http://weare.de.com
*/

class Full_Text_Importer extends Autoblog_Addon {

	/**
	 * Constructor.
	 *
	 * @since 4.1
	 *
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
        	$this->_add_filter( 'autoblog_post_content_before_import', 'get_full_text', 9, 3 );
		$this->_add_action( 'autoblog_feed_edit_form_end', 'add_feed_option', 12, 2 );
	}

    function get_full_text( $old_content, $details, SimplePie_Item $item ) {

        if ( !isset( $details['textcontainerclass'] ) OR $details['textcontainerclass'] == '' ) {
            return $old_content;
        }

        $source_url              = htmlspecialchars_decode( $item->get_permalink() );
        $classname               = $details['textcontainerclass'];

        $doc                     = new DOMDocument();
        $can_use_dom             = @$doc->loadHTMLFile( mb_convert_encoding( $source_url, 'HTML-ENTITIES', 'UTF-8' ) );
        $doc->preserveWhiteSpace = false;

            while ( ( $r = $doc->getElementsByTagName( 'script' ) ) && $r->length ) {
                $r->item(0)->parentNode->removeChild( $r->item(0) );
            }

        $finder                  = new DOMXPath( $doc );
        $node                    = $finder->query( "//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]" );

        return $doc->saveHTML( $node->item(0) );
    }

    /**
     * Renders addon options.
     *
     * @since 4.1
     * @action autoblog_feed_edit_form_end 12 2
     *
     * @param type $key
     * @param type $details
     */
    public function add_feed_option( $key, $details )
    {
        $table = !empty( $details->feed_meta ) ? maybe_unserialize( $details->feed_meta ) : array();

        if ( !isset( $table['textcontainerclass'] ) ) {
            $table['textcontainerclass'] = '';
        }

        // render block header
        $this->_render_block_header( esc_html__( 'Full Text Importer', 'autoblogtext' ) );

        // render block elements
        $this->_render_block_element( esc_html__( 'Text container class', 'autoblogtext' ), sprintf(
            '<input type="text" class="long field" name="abtble[textcontainerclass]" value="%s">',
            esc_attr( stripslashes( $table['textcontainerclass'] ) )
        ) );
    }

}

$fulltextimporter = new Full_Text_Importer();
