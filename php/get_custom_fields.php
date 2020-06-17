<?php

require_once( '../../../../wp-config.php' );

$custom_field_keys = get_post_custom_keys( $_REQUEST['pid'] );

if( is_array( $custom_field_keys ) ) {

	foreach ( $custom_field_keys as $key => $value ) {
	    $valuet = trim($value);
	    if ( '_' == $valuet{0} )
	        continue;
	    // <a class="onmouseover" id="db_fields_'.$column_name.'">{@'.$column_name.'}
	    $cusfie .= '<a class="onmouseover" id="db_cfields_'.$value.'">{@'.$value.'}</a><br />';
	}

	if( !$cusfie )
		$cusfie = 'No custom fields';

	echo $cusfie;

}