<?php

/**
 * Plugin Name: URC Shortcoder
 * Description: This plugin lets you create custom shortcodes and gives you the freedom to display how the contents would look.
 * Version: 1.4.3
 * Author: Jake Almeda
 * Author URI: http://smarterwebpackages.com/
 * Network: true
 * License: GPL2
 */

/*  Copyright 2016  Jake Almeda  (email : jake@smarterwebpackages.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/* --------------------------------------------------------------------------------------------
 * | Register Custom Post Type
 * ----------------------------------------------------------------------------------------- */
add_action( 'init', 'spk_shortcoders' );
function spk_shortcoders() {
    $labels = array(
        'name'               => _x( 'Shortcodes', 'post type general name' ),
        'singular_name'      => _x( 'Shortcode', 'post type singular name' ),
        'menu_name'          => _x( 'Shortcodes', 'admin menu' ),
        'name_admin_bar'     => _x( 'Shortcode', 'add new on admin bar' ),
        'add_new'            => _x( 'Create Shortcode', 'Shortcode' ),
        'add_new_item'       => __( 'Create new Shortcode' ),
        'new_item'           => __( 'New Shortcode' ),
        'edit_item'          => __( 'Edit Shortcode' ),
        'view_item'          => __( 'View Shortcode' ),
        'all_items'          => __( 'All Shortcodes' ),
        'search_items'       => __( 'Search Shortcodes' ),
        'parent_item_colon'  => __( 'Parent Shortcodes:' ),
        'not_found'          => __( 'No Shortcodes found.' ),
        'not_found_in_trash' => __( 'No Shortcodes found in Trash.' )
    );
    
    $args = array(
        'labels'                => $labels,
        'description'           => __( 'Description.' ),
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'spk_shortcoders' ),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'menu_position'         => 50,
        'menu_icon'             => 'dashicons-hammer',
        'supports'              => array( 'title' ),
        'register_meta_box_cb'  => 'spk_shortcodes_boxes',
    );

    register_post_type( 'spk_shortcoders', $args );
    
}

/* --------------------------------------------------------------------------------------------
 * | Metaboxes
 * ----------------------------------------------------------------------------------------- */
add_action( 'add_meta_boxes', 'spk_shortcodes_boxes' );
function spk_shortcodes_boxes() {

    global $post;

    $screen = get_current_screen();
    
    if( 'spk_shortcoders' == $screen->post_type ) {
        add_meta_box( 'spk_shortcoders_box_1', 'Shortcode Slug', 'spk_shortcoders_slug', 'spk_shortcoders', 'normal', 'default' );
        add_meta_box( 'spk_shortcoders_box_2', 'Attributes', 'spk_shortcoders_atts', 'spk_shortcoders', 'normal', 'default' );
        add_meta_box( 'spk_shortcoders_box_3', 'Display Template', 'spk_shortcodes_scbox_code', 'spk_shortcoders', 'normal', 'default' );
        add_meta_box( 'spk_shortcoders_box_4', 'Post References', 'spk_shortcodes_post_opts', 'spk_shortcoders', 'side', 'default' );
    }

    /* Add meta box for the slug (this is the shortcode shortcut)
     * ----------------------------------------------------------------------------------------- */
    if( !function_exists( 'spk_shortcoders_slug' ) ) {
        function spk_shortcoders_slug( $post ) {

            // Use nonce for verification
            wp_nonce_field( plugin_basename( __FILE__ ), 'spk_sc_slug_nonce' );
            
            //global $post;
            echo '<div style="padding:5px 0 10px 0;">Your shortcode will be called by this name. Type the shortcode name above and we\'ll show you the result</div>
                <div><input type="text" name="_spk_shortcoders_slug" id="_spk_shortcoders_slug" value="'.esc_attr( get_post_meta( $post->ID, '_spk_shortcoders_slug', true) ).'" style="width:100%;" /></div>
                <div style="padding-top:10px"><i>Note: Allowed characters: a-z, 0-9, \'_\' (underscore)</i></div>
                <div style="padding-top:25px;" id="sc_div" class="hideme">
                    <span id="this_shortcode"></span>
                    <span>
                        <input type="text" id="this_shortcode_box" style="width:85%; color: #0073aa;" />
                        <img id="btn_copy_sc" src="'.plugin_dir_url( __FILE__ ).'images/btn_copy.png" border="0" style="width:16px; height:auto;" title="Copy shortcode" alt="Copy shortcode" class="onmouseover" />
                        <span id="msg"></span>
                    </span>
                </div>
                <div id="sc_div_gpost_opt" class="hideme spk_info">Info: you can use either id, slug or title attributes to retrieve the entry</div>';

        }
    }

    /* Add meta box for the slug (this is the shortcode shortcut)
     * ----------------------------------------------------------------------------------------- */
    if( !function_exists( 'spk_shortcoders_atts' ) ) {
        function spk_shortcoders_atts( $post ) {

            // Use nonce for verification
            wp_nonce_field( plugin_basename( __FILE__ ), 'spk_sc_atts_nonce' );

            // Get all attributes | the arg 'value' tells the function to return the keys and values fields
            $attr_array = spk_getpostattributes( $post, 'value', NULL, 1 );

            // Set counter
            $att_counter = 0;

            // Set variable
            $att_contents = '';

            // Display
            for( $a=1; $a<=20; $a++ ) {
                
                // show div if there's attribute
                if( !empty( $attr_array[$a-1]['key'] ) ) {
                    $attr_key = $attr_array[$a-1]['key'];
                    $attr_value = $attr_array[$a-1]['value'];
                } else {
                    $attr_key = '';
                    $attr_value = '';
                }


                if( $attr_key ) {
                    $add_class = '';
                    $att_counter++;
                } else {
                    $add_class = 'class="hideme"';
                }

                // validate value if default
                if( $attr_value == 'default_value' ) {
                    $att_val = '';
                } else {
                    $att_val = $attr_value;
                }

                $att_contents .= '<div id="att_div_'.$a.'" '.$add_class.'>
                                    <span><input type="text" id="att_name_'.$a.'" name="att_name_'.$a.'" value="'.$attr_key.'" placeholder="attribute_name" /></span>
                                    <span><input type="text" id="att_val_'.$a.'" name="att_val_'.$a.'" value="'.$att_val.'" placeholder="default_value" /></span>
                                    <span><a id="rem_att_'.$a.'" class="onmouseover">Remove</a></span>
                                </div>';
            }

            // Display information
            //echo '<div style="padding-top:10px;"><i>Note: Allowed characters: a-z, 0-9, \'_\' (underscore)</i></div>';

            // Check if there's a field aa_cpf_postfield
            $aa_cpf = get_post_meta( $post->ID, 'aa_cpf_postfield', false );
            if( $aa_cpf ) {
                if( is_numeric( $aa_cpf[0] ) ) {
                    $aa_cpf_checked = 'checked="checked"';
                    $aa_gpost_checked = '';
                } else {
                    $aa_cpf_checked = '';
                    $aa_gpost_checked = 'checked="checked"';
                }

                // show clear button
                $btn_cpt_clear = '';
            } else {
                $aa_cpf_checked = '';
                $aa_gpost_checked = '';

                // hide clear button
                $btn_cpt_clear = 'class="hideme"';
            }

            // Check if there's a Instagram Embed in the database (cb_insta_opt)
            /*$aa_insta_e = get_post_meta( $post->ID, '_cb_instagram_embed', false );
            if( $aa_insta_e ) {
                $aa_insta_checked = 'checked="checked"';
            } else {*/
                $aa_insta_checked = '';
            //}

            echo '<table border="0" width="100%">
                    <tr>
                        <td valign="top" width="50%"> <!-- #### ATTRIBUTES #### -->
                            <table border="0">
                                <tr>
                                    <td>'.$att_contents.'</td>
                                </tr>
                                <!--tr>
                                    <td style="padding-top:10px;"><i>Note: Allowed characters: a-z, 0-9, \'_\' (underscore)</i></td>
                                </tr-->
                                <tr>
                                    <td style="padding-top:20px;"><a class="page-title-action" id="add_att">Add Attribute</a><input type="hidden" id="att_counter" value="'.$att_counter.'" /></td>
                                </tr>
                            </table>
                        </td>
                        <td valign="top"> <!-- #### OTHER OPTIONS #### -->
                            <table border="0">
                                <tr>
                                    <th align="left">Post / Page / <span title="Custom Post Type">CPT</span> <span id="cpt_clear" '.$btn_cpt_clear.'"><a class="onmouseover">(Clear)</a></span></th>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="radio" name="cb_ppc_opt" id="ppc_opt" value="link" '.$aa_cpf_checked.' /><span id="ppc_opt_text" class="onmouseover">Link</span>
                                        &nbsp;&nbsp;&nbsp;
                                        <input type="radio" name="cb_ppc_opt" id="gpost_opt" value="get" '.$aa_gpost_checked.' /><span id="gpost_opt_text" class="onmouseover">Get</span>
                                    </td>
                                </tr>
                                <!--tr>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <th align="left">Embeds</th>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="cb_insta_opt" id="insta_opt" '.$aa_insta_checked.' /><span id="insta_opt_text" class="onmouseover">Instagram</span></td>
                                </tr-->
                            </table>
                        </td>
                    </tr>
                </table>';

            // Show option
            /*echo '<div style="padding-top:20px;">
                    <table border="0">
                        <tr>
                            <td><a class="page-title-action" id="add_att">Add Attribute</a></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <input type="hidden" id="att_counter" value="'.$att_counter.'" />
                                <input type="checkbox" name="cb_ppc_opt" id="ppc_opt" '.$aa_cpf_checked.' /><span id="ppc_opt_text" class="onmouseover">Show Post/Page/CPT fields</span>
                            </td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><input type="checkbox" name="cb_insta_opt" id="insta_opt" '.$aa_insta_checked.' /><span id="insta_opt_text" class="onmouseover">Embed Instagram</span></td>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td><input type="checkbox" name="cb_get_post" id="gpost_opt" '.$aa_gpost_checked.' /><span id="gpost_opt_text" class="onmouseover">Get Post/Page</span></td>
                        </tr>
                    </table>
                </div>';
            */
        }
    }

    /* Add meta box for the code (this is like Pods template)
     * ----------------------------------------------------------------------------------------- */
    if( !function_exists( 'spk_shortcodes_scbox_code' ) ) {
        function spk_shortcodes_scbox_code( $post ) {

            // Use nonce for verification
            wp_nonce_field( plugin_basename( __FILE__ ), 'spk_sc_codes_nonce' );

            // place holder to put the attributes
            echo '<div style="padding: 5px 0 15px 0" id="spk_att_list"></div>';

            // retrieve field value
            $field_value = get_post_meta( $post->ID, '_spk_shortcoders_code', false );

            // Settings that we'll pass to wp_editor
            $args = array (
                'tinymce' => false,
                'quicktags' => false,
                'media_buttons' => false,
            );
            
            // validate array
            if( is_array( $field_value ) && !empty( $field_value[0] ) ) {
                $fvalue = $field_value[0];
            } else {
                $fvalue = '';
            }
            echo '<div>'.wp_editor( $fvalue, '_spk_shortcoders_code', $args ).'</div>';
            //echo '<textarea name="_spk_shortcoders_code" id="_spk_shortcoders_code" rows="10" cols="100">'.$field_value[0].'</textarea>';

        }
    }

    /* Add meta box for the code (this is like Pods template)
     * ----------------------------------------------------------------------------------------- */
    if( !function_exists( 'spk_shortcodes_post_opts' ) ) {
        function spk_shortcodes_post_opts( $post ) {

            // Use nonce for verification
            wp_nonce_field( plugin_basename( __FILE__ ), 'spk_sc_post_opts_nonce' );

            global $wpdb;

            $args = array( 'public' => true );
            $output = 'objects'; // names or objects, note names is the default
            $operator = 'and'; // 'and' or 'or'

            // add default value
            $postnames = '<option value="default_value">Select Post Type</option>';

            // Check if there's a field aa_cpf_postfield
            $aa_cpf = get_post_meta( $post->ID, 'aa_cpf_postfield', false );
            if( is_numeric( $aa_cpf[0] ) ) {
                $aa_cpf_value = $aa_cpf[0];
            } else {
                $thisposttype = $aa_cpf[0];
            }

            // used for detecting if in edit mode
            $edited = 0;

            $post_types = get_post_types( $args, $output, $operator ); 

            foreach ( $post_types as $post_type ) {

                $post_type_name = $post_type->name;

                if( $post_type_name != 'spk_shortcoders' ) {
                    
                    // Validate if post is in EDIT mode
                    if( $thisposttype && $post_type_name == $thisposttype || $aa_cpf_value && $post_type_name == get_post_type( $aa_cpf_value ) ) {
                        $ptchecked = 'selected="selected"';

                        $show_entries = $post_type_name;

                        $edited++;
                    } else {
                        $ptchecked = '';
                    }

                    // set post options
                    $postnames .= '<option value="'.$post_type_name.'" '.$ptchecked.'>'.$post_type->label.'</option>';

                    // get entries
                    $arg = array(
                        'orderby'          => 'date',
                        'order'            => 'DESC',
                        'post_type'        => $post_type_name,
                        'post_status'      => 'publish'
                    );

                    // determine the post type
                    if( $post_type_name == 'post' ) {
                        $post_entries = get_entries( get_posts( $arg ), $aa_cpf_value );
                    } elseif( $post_type_name == 'page' ) {
                        $page_entries = get_entries( get_pages( $arg ), $aa_cpf_value );
                    } elseif( $post_type_name == 'attachment' ) {
                        $attc_entries = get_attachments( 'attachment', $aa_cpf_value );
                    } else {
                        // check if post has been selected
                        if( $show_entries == $post_type_name ) {
                            $thisclass = '';
                        } else {
                            $thisclass = 'class="hideme"';
                        }

                        $dyna_entries .= '<select name="dt_'.$post_type_name.'" id="dt_'.$post_type_name.'" '.$thisclass.' style="width:100%;">'.get_cp_entries( $post_type_name, $aa_cpf_value ).'</select>';
                    }

                }

            }

            // get database table columns
            foreach ( get_wp_posts_fields() as $gwpf ) {
                $col_names .= '<tr><td><a class="onmouseover" id="db_fields_'.$gwpf.'">{@'.$gwpf.'}</td></tr>';
            }

            // check if SELECT should be shown or hidden
            if( !function_exists( hidethisfield ) ) {
                function hidethisfield( $show_entries, $posttype ) {
                    if( $show_entries == $posttype ) {
                        $return = '';
                    } else {
                        $return = 'class="hideme"';
                    }

                    return $return;
                }
            }

            if( $edited ) {
                $hidetable = '';
            } else {
                $hidetable = 'class="hideme"';
            }
            
            // display
            echo '<table border="0" class="full-width">
                    <tr>
                        <td style="padding:5px;">
                            <select name="dtm_post_type" id="dtm_post_type" style="width:100%;">'.$postnames.'</select>
                            <br />
                            <select name="dt_post" id="dt_post" '.hidethisfield( $show_entries, "post" ).' style="width:100%;">'.$post_entries.'</select>
                            <select name="dt_page" id="dt_page" '.hidethisfield( $show_entries, "page" ).' style="width:100%;">'.$page_entries.'</select>
                            <select name="dt_attachment" id="dt_attachment" '.hidethisfield( $show_entries, "attachment" ).' style="width:100%;">'.$attc_entries.'</select>
                            '.$dyna_entries.'
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:5px;">
                            <table border="0" id="db_colnames" '.$hidetable.'>
                                <tr>
                                    <th align="left">Fields</th>
                                </tr>
                                '.$col_names.'
                                <tr>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <th align="left">Taxonomy</th>
                                </tr>
                                <tr>
                                    <td style="padding:5px;" id="puttaxeshere"></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                </tr>
                                <tr>
                                    <th align="left">Custom Fields</th>
                                </tr>
                                <tr>
                                    <td><i>These fields only show up depending on the article id</i></td>
                                </tr>
                                <tr>
                                    <td style="padding:5px;" id="putcustomfieldshere"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>';

        }
    }
}

/* --------------------------------------------------------------------------------------------
 * | Retrieve the WP_POSTS database table columns
 * ----------------------------------------------------------------------------------------- */
function get_wp_posts_fields() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'posts';

    foreach ( $wpdb->get_col( "DESC " . $table_name, 0 ) as $column_name ) {
        
        $col_names[] = $column_name;

    }

    return $col_names;
}

/* --------------------------------------------------------------------------------------------
 * | Validate if post is selected
 * ----------------------------------------------------------------------------------------- */
function get_entries( $posts_array, $pid ) {

    foreach ( $posts_array as $post ) {

        if( $pid && $pid == $post->ID ) {
            $sel = 'selected="selected"';
        } else {
            $sel = '';
        }

        $return .= '<option value="'.$post->ID.'" '.$sel.'>'.$post->post_title.'</option>';

    }

    return $return;
}

/* --------------------------------------------------------------------------------------------
 * | Get entries for the WP Built in post type - attachments
 * ----------------------------------------------------------------------------------------- */
function get_attachments( $arg, $pid ) {
    $query_images_args = array(
        'post_type' => $arg,
        'post_mime_type' =>'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
    );

    $query_images = new WP_Query( $query_images_args );
    //$images = array();
    foreach ( $query_images->posts as $image) {

        if( $pid && $pid == $image->ID ) {
            $sel = 'selected="selected"';
        } else {
            $sel = '';
        }

        $return .= '<option value="'.$image->ID.'" '.$sel.'>'.$image->post_title.'</option>';

    }

    // Restore original Post Data
    wp_reset_query();

    return $return;
}

/* --------------------------------------------------------------------------------------------
 * | Get entries for a Custom Post Type
 * ----------------------------------------------------------------------------------------- */
function get_cp_entries( $post_type_name, $pid ) {

    global $wpdb;

    $spk_postatts = $wpdb->get_results(
            "SELECT DISTINCT id, post_title
            FROM
            ".$wpdb->prefix."posts
            WHERE
            post_type='".$post_type_name."' and
            post_status = 'publish'", OBJECT );

    foreach( $spk_postatts as $atts ) {
        if( $pid && $pid == $atts->id ) {
            $sel = 'selected="selected"';
        } else {
            $sel = '';
        }

        $return .= '<option value="'.$atts->id.'" '.$sel.'>'.$atts->post_title.'</option>';
    }

    return $return;

}

/* --------------------------------------------------------------------------------------------
 * | Get Attribute names and values
 * ----------------------------------------------------------------------------------------- */
function spk_getpostattributes( $post, $field, $pid, $cfields ) {

    global $wpdb, $post;

    if( $pid ) {
        $this_id = $pid;
    } else {

        if( is_object( $post ) ) {
            $this_id = $post->ID;    
        } else {
            $this_id = '';
        }
        
    }
    
    $spk_postatts = $wpdb->get_results(
            "select b.meta_key, b.meta_value
            from
            ".$wpdb->prefix."posts a
            inner join ".$wpdb->prefix."postmeta b
            on a.id = b.post_id
            where
            a.id='".$this_id." '", OBJECT );
    
    foreach( $spk_postatts as $atts ) {
        
        // check if $key starts with underscore (_); attribute names don't start with it
        $exp_key = explode( '_', $atts->meta_key );
        if( trim( $exp_key[0] ) ) {
            // not empty
            
            // aa_cpf = custom post field
            if( trim( $exp_key[0] ) == 'aa' && $cfields ) {
                // this is for the post (custom) fields
                
            } else {
                // this is the shortcode attribute
                if( $field == 'key' ) {
                    $return[] = $atts->meta_key;
                } else {
                    $return[] = array( 'key' => $atts->meta_key, 'value' => $atts->meta_value );
                }
            }

        }

    }
    
    if( !empty( $return ) ) {
        return $return;
    }
    

    // Restore original Post Data
    wp_reset_postdata();
}

/* --------------------------------------------------------------------------------------------
 * | Save metaboxes
 * ----------------------------------------------------------------------------------------- */
add_action('save_post', 'spk_save_shortcodes_meta', 1, 2); // save the custom fields
function spk_save_shortcodes_meta( $post_id, $post ) {

    // Check permissions
    if ( !current_user_can( 'edit_post', $post->ID ) )
        return $post->ID;

    // OK, we're authenticated: we need to find and save the data

    // Slug
    // ---------------------
    spk_save_slug_code( '_spk_shortcoders_slug', $post_id, $post, 1 );

    // Attributes
    // ---------------------
    spk_save_atts( $post_id, $post );

    // Code
    // ---------------------
    spk_save_slug_code( '_spk_shortcoders_code', $post_id, $post, 0 );

    // Post / Page - fields / custom fields
    // ---------------------
    spk_save_cfields( $post_id, $post );

    // Embed Instagram
    // ---------------------
    //spk_save_insta( $post_id, $post );

}

/* --------------------------------------------------------------------------------------------
 * | Save Slug
 * ----------------------------------------------------------------------------------------- */
function spk_save_slug_code( $nonce_field, $post_id, $post, $checker ) {

    // Get the posted data and sanitize it for use as an HTML class
    if(  $checker == 1 ) {
        // slug
        $value = ( isset( $_POST[ $nonce_field ] ) ? sanitize_html_class( $_POST[ $nonce_field ] ) : '' );
    } else {
        // code - we don't want to remove any html tags
        if( isset( $_POST[ $nonce_field ] ) ) {
            $value = $_POST[ $nonce_field ];
        } else {
            $value = '';
        }
    }

    // Get the meta key
    $key = $nonce_field;

    // Save or Update
    if( get_post_meta($post->ID, $key, false) ) {
        // If the custom field already has a value
        update_post_meta($post->ID, $key, $value);
    } else {
        // If the custom field doesn't have a value
        add_post_meta($post->ID, $key, $value);
    }

    // Delete if empty
    if(!$value) delete_post_meta($post->ID, $key); // Delete if blank
}

/* --------------------------------------------------------------------------------------------
 * | Save Attributes
 * ----------------------------------------------------------------------------------------- */
function spk_save_atts( $post_id, $post ) {

    $meta_key_fields = array(); // declare an empty array

    // Save
    // -----------
    for( $a=1; $a<=20; $a++ ) {

        if( isset( $_POST[ 'att_name_'.$a ] ) ) {
            $meta_key = $_POST[ 'att_name_'.$a ];
        } else {
            $meta_key = NULL;
        }

        if( isset( $_POST[ 'att_val_'.$a ] ) ) {
            $meta_value = $_POST[ 'att_val_'.$a ];
        } else {
            $meta_value = NULL;
        }
        
        // check if name has value; skip if empty
        if( $meta_key ) {
            
            // validate first if attribute has a default value set
            if( $meta_value ) {

                // Get the posted data and sanitize it for use as an HTML class
                $value = ( isset( $meta_value ) ? sanitize_html_class( $meta_value ) : '' );

            } else {

                $value = 'default_value';

            }
            
            // Get the meta key
            $key = ( isset( $meta_key ) ? sanitize_html_class( $meta_key ) : '' );

            // Save or Update
            if( get_post_meta($post->ID, $key, false) ) {
                // If the custom field already has a value
                update_post_meta($post->ID, $key, $value);
            } else {
                // If the custom field doesn't have a value
                add_post_meta($post->ID, $key, $value);
            }

            // put meta_keys in an array - it'll be used to check attributes that have been removed for deletion below
            $meta_key_fields[] = $meta_key;
            
        }

    }

    // Delete
    // -----------
    // Get all attributes | the arg 'key' tells the function to return that field only
    $attr_array = spk_getpostattributes( $post, 'key', NULL, NULL );

    if( is_array( $attr_array ) ) {

        foreach ($attr_array as $val) {

            if( is_array( $meta_key_fields ) && !empty( $meta_key_fields ) ) {
                // WHAT IF THERE ARE 0 ATTRIBUTE FIELDS!!!!!!!!!!!!!!!!!!!!!
                if( !in_array( $val, $meta_key_fields ) ) {
                    // field has been removed in UI; delete in DB
                    delete_post_meta($post->ID, $val); // Delete if blank
                }
            } else {
                // all attributes have been removed
                delete_post_meta($post->ID, $val); // Delete if blank
            }

        }

    }

}

/* --------------------------------------------------------------------------------------------
 * | Save Post / Page - fields / custom fields
 * ----------------------------------------------------------------------------------------- */
function spk_save_cfields( $post_id, $post ) {
    
    // is page options shown
    $cb_ppc_opt = isset( $_POST[ 'cb_ppc_opt' ] );

    // get selected post
    $pname = isset( $_POST[ 'dtm_post_type' ] );

    // key name
    $key = 'aa_cpf_postfield';

    if( $cb_ppc_opt && isset( $pname ) && $pname != 'default_value' ) {

        // post entry
        //$pentry = $_POST[ 'dt_'.$pname ];

        // get the post/page entry
        if( $_POST[ 'cb_ppc_opt' ] == 'link' ) {
            $value = $_POST[ 'dt_'.$pname ];
        } else {
            $value = $pname;
        }

        // Save or Update
        if( get_post_meta( $post->ID, $key, false ) ) {
            // If the custom field already has a value
            update_post_meta( $post->ID, $key, $value );
        } else {
            // If the custom field doesn't have a value
            add_post_meta( $post->ID, $key, $value );
        }

    } else {

        // Delete if blank
        delete_post_meta( $post->ID, $key ); 

    }

}

/* --------------------------------------------------------------------------------------------
 * | Save Post / Page - fields / custom fields
 * ----------------------------------------------------------------------------------------- */
/*function spk_save_insta( $post_id, $post ) {
    
    // is page options shown
    $cb_insta_opt = isset( $_POST[ 'cb_insta_opt' ] );

    $key = '_cb_instagram_embed';
    $value = 1;

    if( $cb_insta_opt ) {

        // Save or Update
        if( get_post_meta( $post->ID, $key, false ) ) {
            // If the custom field already has a value
            update_post_meta( $post->ID, $key, $value );
        } else {
            // If the custom field doesn't have a value
            add_post_meta( $post->ID, $key, $value );
        }

    } else {

        // Delete if blank
        delete_post_meta( $post->ID, $key ); 

    }

}*/

/* --------------------------------------------------------------------------------------------
 * | Change "Enter title here"
 * ----------------------------------------------------------------------------------------- */
add_filter( 'enter_title_here', 'spk_change_shortcode_title' );
function spk_change_shortcode_title( $title ){
    
    $screen = get_current_screen();
    
    if( 'spk_shortcoders' == $screen->post_type ) {
        $title = "Shortcode name";
    }
    return $title;
    
}

/* --------------------------------------------------------------------------------------------
 * | Add class to the meta box for post options
 * ----------------------------------------------------------------------------------------- */
add_filter( 'postbox_classes_spk_shortcoders_spk_shortcoders_box_4', 'spk_add_metabox_classes' );
function spk_add_metabox_classes( $classes = array() ) {

    // add classes to the metabox here
    $add_classes = array( 'hideme' );

    foreach ( $add_classes as $class ) {

        if ( ! in_array( $class, $classes ) ) {

            $classes[] = sanitize_html_class( $class );

        }

    } // End of foreach loop

    return $classes;

}

/* --------------------------------------------------------------------------------------------
 * | Add custom columns
 * ----------------------------------------------------------------------------------------- */
add_filter( 'manage_edit-spk_shortcoders_columns', 'my_spk_shortcoders_columns' ) ;
function my_spk_shortcoders_columns( $columns ) {

    $columns = array(
        'cb' => '<input type="checkbox" />',
        'title' => __( 'Name' ),
        'shortcode' => __( 'Shortcodes' ),
        'attributes' => __( 'Attributes' ),
        'date' => __( 'Date' )
    );

    return $columns;
}

/* --------------------------------------------------------------------------------------------
 * | Add content to the CPT column(s)
 * ----------------------------------------------------------------------------------------- */
add_action( 'manage_spk_shortcoders_posts_custom_column', 'my_manage_spk_shortcoders_columns', 10, 2 );
function my_manage_spk_shortcoders_columns( $column, $post_id ) {
    global $post;

    switch( $column ) {

        // If displaying the 'shortcode' column
        case 'shortcode' :

            $sc_code = get_post_meta( $post->ID, '_spk_shortcoders_slug', true );
            echo __( '['.$sc_code.'] [/'.$sc_code.']' );

            break;

        // If displaying the 'attributes' column
        case 'attributes' :

            // Get all attributes | the arg 'key' tells the function to return that field only
            $attr_array = spk_getpostattributes( $post, 'key', NULL, NULL );

            if ( is_array( $attr_array ) ) {

                $x = 1;

                foreach( $attr_array as $val ) {

                    echo __( $val );

                    if( $x < count( $attr_array ) ) {
                        echo __( ', ' );
                    }

                    $x++;
                }
            }

            break;
    }
}

/* --------------------------------------------------------------------------------------------
 * | Enqueue scripts | Load only in administrator pane
 * ----------------------------------------------------------------------------------------- */
add_action( 'admin_enqueue_scripts', 'spk_sc_enqueue_scripts' );
function spk_sc_enqueue_scripts() {

    // enqueue needed native jQuery files
    /*if( !wp_script_is( 'jquery-ui-core', 'enqueued' ) ) {
        wp_enqueue_script( 'jquery-ui-core' );
    }*/

    if( !wp_script_is( 'jquery-effects-core', 'enqueued' ) ) {
        wp_enqueue_script( 'jquery-effects-core' );
    }

    if( !wp_script_is( 'jquery-effects-fade', 'enqueued' ) ) {
        wp_enqueue_script( 'jquery-effects-fade' );
    }

    // load js on specific screen only
    if( 'spk_shortcoders' == get_current_screen()->post_type ) {
        // last arg is true - will be placed before </body>
        //wp_enqueue_script( 'spk_shortcoder_js', plugins_url( 'js/asset_min.js', __FILE__ ), NULL, NULL, true );
        wp_register_script( 'spk_shortcoder_js', plugins_url( 'js/asset_min.js', __FILE__ ), NULL, NULL, TRUE );
     
        // Localize the script with new data
        $translation_array = array(
            'spk_scj_ajax' => plugin_dir_url( __FILE__ ).'/php/',
        );
        wp_localize_script( 'spk_shortcoder_js', 'spk_scj', $translation_array );
         
        // Enqueued script with localized data.
        wp_enqueue_script( 'spk_shortcoder_js' );

        // enqueue styles
        wp_enqueue_style( 'spk_shortcoder_css', plugin_dir_url( __FILE__ ).'css/style_min.css' );
    }

}


/* --------------------------------------------------------------------------------------------
 * | END OF ADMINISTRATOR SCRIPTS
 * ----------------------------------------------------------------------------------------- */

/* --------------------------------------------------------------------------------------------
 * | START OF PUBLIC SCRIPTS
 * ----------------------------------------------------------------------------------------- */


/* --------------------------------------------------------------------------------------------
 * | Set global variable
 * ----------------------------------------------------------------------------------------- */
//global $has_instagram_embed;

/* --------------------------------------------------------------------------------------------
 * | Validate screen - should not be admin
 * ----------------------------------------------------------------------------------------- */
if ( !is_admin() ) {
    add_action( 'init', 'spk_shortcoders_pub' );
}

/* --------------------------------------------------------------------------------------------
 * | Enqueue Shortcodes
 * ----------------------------------------------------------------------------------------- */
function spk_shortcoders_pub() {

    // query all post with post_type='spk_shortcoders'
    global $wpdb; //, $has_instagram_embed;
    
    // query all shortcodes
    $posts = $wpdb->get_results( "SELECT DISTINCT id FROM ".$wpdb->prefix."posts
                                 WHERE post_status = 'publish' and post_type = 'spk_shortcoders'", OBJECT );
    foreach( $posts as $post ) {
        
        // get id
        $pid = $post->id;
        
        // get sc slug
        $the_slug = get_post_meta( $pid, '_spk_shortcoders_slug', true);
        if( !empty( $the_slug ) ) {
            $sc_slug = $the_slug;
        }

        // get sc code (template)
        $the_code = get_post_meta( $pid, '_spk_shortcoders_code', true);
        if( !empty( $the_code ) ) {
            $sc_code = $the_code;
        }

        // validate embed Instagram option
        // add sc slug to an array as basis for loading external Instagram js scripts
        /*$the_instagram = get_post_meta( $pid, '_cb_instagram_embed', true);
        if( !empty( $the_instagram ) ) {
            $has_instagram_embed[] = $the_slug;
        }*/

        // validate and proceed if all attributes are collected
        if( $sc_slug && $sc_code ) {

            // create dynamic functions
            // use -> it gets the local variables within the code and uses them inside the function
            $sc_this = function( $atts, $content = null ) use ( $pid, $sc_code ) {

                // execute all shortcodes found within the shortcode template
                $sc_code = do_shortcode( $sc_code );

                // loop through each attribute (specified)
                if( is_array( $atts ) ) {
                    foreach( $atts as $key => $value ) {
                        // check if value is set by the user, get default value if empty
                        if( !$value ) {
                            $value = get_post_meta( $pid, $key, true );
                        }

                        $sc_code = str_replace( '{@'.$key.'}', $value, $sc_code);
                    }
                }

                // clean up - remove attributes that were not specified
                $att_keys = spk_getpostattributes( NULL, 'key', $pid, NULL );
                if( is_array( $att_keys ) ) {
                    foreach ($att_keys as $val) {
                        // remove unwanted spaces BEFORE and AFTER the attribute (identifier)
                        $sc_code = str_replace( ' {@'.$val.'} ', '', $sc_code);

                        // OR remove the attribute (identifier) without spaces
                        $sc_code = str_replace( '{@'.$val.'}', '', $sc_code);
                    }
                }
                
                // set 'get' attributes for reference when pulling post details
                $get_atts = array( 'id', 'slug', 'title' );

                // search for custom post types
                $aa_cpf = get_post_meta( $pid, 'aa_cpf_postfield', true );
                if( $aa_cpf ) {

                    // POST - INFORMATION
                    if( is_numeric( $aa_cpf ) ) {
                        // check for wp_post database fields and replace
                        foreach( get_wp_posts_fields() as $gwpf ) {
                            $contents = get_post_field( $gwpf, $aa_cpf, $context = 'display' );
                            
                            // check if column required is post_content
                            if( $gwpf == 'post_content' ) {
                                // execute shortcodes within the content
                                $sc_code = str_replace( '{@'.$gwpf.'}', do_shortcode( $contents ), $sc_code );
                            } else {
                                $sc_code = str_replace( '{@'.$gwpf.'}', $contents, $sc_code );
                            }
                        }

                        // check for custom fields
                        foreach( get_post_custom_keys( $aa_cpf ) as $gpck ) {
                            $contents_cust = get_post_custom_values( $gpck, $aa_cpf );
                            
                            $sc_code = str_replace( '{@'.$gpck.'}', $contents_cust[0], $sc_code );
                        }
                    } else {
                        // GET - LOOP THROUGH PRE DEFINED ATTRIBUTES
                        foreach ( $get_atts as $gvals ) {
                            // LOOP THROUGH USER SPECIFIED ATTRIBUTE
                            foreach( $atts as $key => $value ) {
                                // VALIDATE WHAT IS USED
                                if( $gvals == $key ) {
                                    
                                    if( $key == 'id' || $key == 'slug' || $key == 'title' ) {
                                        
                                        // LOOP EACH DATABASE TABLE COLUMNS
                                        foreach( get_wp_posts_fields() as $gwpf ) {
                                            // DISPLAY POST AUTHOR
                                            if( $gwpf == 'post_author' ) {
                                                $contents = get_the_author_meta( 'display_name', spk_get_post_id( $atts[ $key ] ) );
                                            } elseif( $gwpf == 'post_content' ) {
                                                // check if column required is post_content
                                                // execute shortcodes within the content
                                                $contents = do_shortcode( get_post_field( $gwpf, spk_get_post_id( $atts[ $key ] ), $context = 'display' ) );
                                            } else {
                                                $contents = get_post_field( $gwpf, spk_get_post_id( $atts[ $key ] ), $context = 'display' );
                                            }
                                            
                                            $sc_code = str_replace( '{@'.$gwpf.'}', $contents, $sc_code );
                                        }

                                        // POST - TAXONOMIES
                                        $these_taxes = spk_get_my_taxes( spk_get_post_id( $atts[ $key ] ) );
                                        if( is_array( $these_taxes ) ) {

                                            foreach( $these_taxes as $taxi ) {
                                                $count_tax = count( wp_get_post_terms( spk_get_post_id( $atts[ $key ] ), $taxi ) );
                                                if( $count_tax == 1 ) {
                                                    $contents = wp_get_post_terms( spk_get_post_id( $atts[ $key ] ), $taxi )[0]->name;
                                                } else {
                                                    $w = 1;

                                                    foreach ( wp_get_post_terms( spk_get_post_id( $atts[ $key ] ), $taxi ) as $taxx ) {
                                                        //$set_tax[] = $taxx->name;

                                                        $tax_names .= $taxx->name;

                                                        if( $w == ( $count_tax - 1) ) {
                                                            $tax_names .= ' and ';
                                                        } else {
                                                            if( $w < $count_tax ) {
                                                                $tax_names .= ', ';
                                                            }
                                                        }

                                                        $w++; // counter
                                                    }

                                                    $contents = $tax_names;
                                                }
                                                
                                                $sc_code = str_replace( '{@'.$taxi.'}', $contents, $sc_code );

                                                // unset array for next loop
                                                unset( $set_tax );
                                            }

                                        }

                                    }

                                } // if( $gvals == $key ) {

                            }

                        } // foreach ( $get_atts as $gvals ) {
                    }

                }
                
                // apply string replace to {@content} (this is the content between the opening and closing tags [sc]this is it[/sc])
                $sc_code = str_replace( '{@content}', do_shortcode( $content ), $sc_code );

                return $sc_code;

            };
            // register shortcode
            add_shortcode( $sc_slug, $sc_this );

        }

    }
    
    // reset default post
    wp_reset_postdata();

}

/* --------------------------------------------------------------------------------------------
 * | Get all taxonomies for the post
 * ----------------------------------------------------------------------------------------- */
function spk_get_my_taxes( $pid ) {

    $taxes = get_post_taxonomies( $pid );

    if( is_array( $taxes ) ) {
        foreach( $taxes as $val ) {
            $taxy[] = $val;
        }
    }

    return $taxy;
}

/* --------------------------------------------------------------------------------------------
 * | Get the Post ID via ID, Slug or Post Title
 * ----------------------------------------------------------------------------------------- */
function spk_get_post_id( $thispost ) {
    
    global $wpdb;

    if( is_numeric( $thispost ) ) {
        // ID was used
        // ----------------------------------------
        $arg = $thispost;
        
    } else {

        if( is_page( $thispost ) ) {
            // Title was used
            // ----------------------------------------
            $arg = get_page_by_title( $thispost );
            
        } else {
            // Slug for PAGES
            // ----------------------------------------
            $page = get_page_by_path( $thispost , OBJECT );
            if( isset( $page ) ) {
                $arg = get_page_by_title( $page->post_title );
            }
            
            // Slug or Title for POST
            // ----------------------------------------
            if( !$arg ) {
                
                // check if var has spaces - spaces means, var is a title. Otherwise, its a slug
                $exp_thispost = explode( " ", $thispost );
                if( count( $exp_thispost ) > 1 ) {
                    // title
                    $extra_arg = "post_title = '".$thispost."' ";
                } else {
                    // slug
                    $extra_arg = "post_name = '".$thispost."' ";
                }
                
                global $wpdb;
                $query = $wpdb->get_results( "SELECT ID FROM ".$wpdb->prefix."posts WHERE ".$extra_arg, OBJECT );
                $arg = $query[0]->ID;
                wp_reset_postdata();
            }
            
        }
    }

    return $arg;

}

/* --------------------------------------------------------------------------------------------
 * | Async load function
 * ----------------------------------------------------------------------------------------- */
/*add_filter( 'clean_url', 'spk_async_def_scripts', 11, 1 );
function spk_async_def_scripts($url) {
    if ( strpos( $url, '#asyncload') === false )
        return $url;
    else if ( is_admin() )
        return str_replace( '#asyncload', '', $url );
    else
        return str_replace( '#asyncload', '', $url )."' defer async='async"; 
}*/

/* --------------------------------------------------------------------------------------------
 * | Enqueue Instagram external script if Embed Instagram option is ticked
 * | external script will only be loaded for post/pages that have specific shortcode used
 * ----------------------------------------------------------------------------------------- */
/*add_action( 'wp_enqueue_scripts', 'spk_async_def_theme_scripts');
function spk_async_def_theme_scripts() {

    global $post, $has_instagram_embed;

    if( is_array( $has_instagram_embed ) ) {
        foreach( $has_instagram_embed as $sc ) {
            if( has_shortcode( $post->post_content, $sc ) ) {
                wp_enqueue_script( 'spk_add_async_this_external', '//platform.instagram.com/en_US/embeds.js#asyncload', NULL, NULL, true );
            }   
        }
    }

}*/