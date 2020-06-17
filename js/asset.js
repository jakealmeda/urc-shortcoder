var FadeSpeed = 'fast', FileLoc = spk_scj.spk_scj_ajax, MaxAtts = 20;

jQuery( document ).ready( function () {

    // hide the Slug option in Screen Options
    jQuery( 'label[for="slugdiv-hide"]' ).hide();

    // select the checkbox for Post References - WP loads the screen without it selected
    if( !jQuery( '#spk_shortcoders_box_4-hide' ).is( ":checked" ) ) {
        jQuery( '#spk_shortcoders_box_4-hide' ).prop( 'checked', true );
    }

    // hide the entire Screen Options checkboxes for which ones are shown/not
    //jQuery( 'fieldset[class="metabox-prefs"]' ).hide();

    // blur on load
    for( a = 1; a <= MaxAtts; a++ ) {
        //Set the initial blur (unless its highlighted by default)
        if( jQuery( '#att_name_' + a ).val().length == 0 ) {
            inputBlur( jQuery( '#att_name_' + a ) );
        }

        if( jQuery( '#att_val_' + a ).val().length == 0 ) {
            inputBlur( jQuery( '#att_val_' + a ) );
        }

        jQuery( '#att_name_' + a, '#att_val_' + a ).blur( function () {
            inputBlur( this );
        });

        jQuery( '#att_name_' + a, '#att_val_' + a ).focus( function () {
            inputFocus( this );
        });
    }

    // show shortcode
    if( jQuery( '#_spk_shortcoders_slug' ).val().length > 0 ) {
        // set shortcode value
        ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );

        // show div for shortcode
        SCdivShow();
    } else {

        // hide div for shortcode
        SCdivHide();
    }
    
    // Display Attributes above the display template
    ListAttributesDisplayed();

    // If post is in EDIT mode, validate if Show Post/Page/CPT fields checkbox is checked
    if( jQuery( '#ppc_opt' ).is( ":checked" ) || jQuery( '#gpost_opt' ).is( ":checked" ) ) {
        
        if( jQuery( '#spk_shortcoders_box_4' ).hasClass( "hideme" ) ) {
            // remove hidden class
            jQuery( '#spk_shortcoders_box_4' ).removeClass( 'hideme' );
        }

        // show custom fields
        CheckForVisibleSelect();

    }

    // Disable entry dropdowns on load if get radio button is selected
    if( jQuery( '#gpost_opt' ).is( ":checked" ) ) {
        // disable post/page/cpt entries dropdown option
        EnableDisableDropDowns( 'disable' );
    }

    // wordpress adds the class hide-if-js to the metabox to hide it- this will remove it that class
    if( jQuery( '#spk_shortcoders_box_4' ).hasClass( 'hide-if-js' ) ) {
        jQuery( '#spk_shortcoders_box_4' ).removeClass( 'hide-if-js' );
    }

});

/* ------------------------------------------------
 * Remove class to show SC div
 * --------------------------------------------- */
function SCdivShow() {
    if( jQuery( '#sc_div' ).is(":hidden") ) {
        jQuery( '#sc_div' ).removeClass( 'hideme' );
    }
}

/* ------------------------------------------------
 * Add class to hide SC div
 * --------------------------------------------- */
function SCdivHide() {
    if( jQuery( '#sc_div' ).is(":visible") ) {
        jQuery( '#sc_div' ).addClass( 'hideme' );
    }
}

/* ------------------------------------------------
 * When focused
 * --------------------------------------------- */
function inputFocus(i) {
    if (i.value == i.defaultValue) {
        i.value = "";
        jQuery(i).removeClass("blurredDefaultText");
    }
}

/* ------------------------------------------------
 * When off focus
 * --------------------------------------------- */
function inputBlur(i) {
    if (i.value == "" || i.value == i.defaultValue) {
        i.value = i.defaultValue;
        jQuery(i).addClass("blurredDefaultText");
    }
}

/* ------------------------------------------------
 * Title - focus in
 * --------------------------------------------- */
jQuery( '#title' ).focusin( function() {
    jQuery( '#title, #title-prompt-text' ).removeAttr('style');
});

/* ------------------------------------------------
 * Title - focus out
 * --------------------------------------------- */
jQuery( '#title' ).focusout( function() {
    
    // check if title is empty
    if( jQuery( this ).val().length == 0 ) {
        // apply reddish background color to the textbox
        jQuery( this ).css( "background-color", "#FF6347" );

        // change font color to the label
        jQuery( '#title-prompt-text' ).css( "color", "#fff" );

        // show error message
        jQuery( '#this_shortcode' ).html( 'Please enter a shortcode name' );

        if( jQuery( '#_spk_shortcoders_slug' ).val().length == 0 ) {
            // hide div
            SCdivHide();

            // empty hidden shortcode textbox
            jQuery( '#this_shortcode_box').val( '' );
        }
    } else {
        // user might have placed a slug first before the title
        if( jQuery( '#_spk_shortcoders_slug' ).val().length == 0 ) {
            var FilteredString = FilterString( jQuery( this ).val() );

            // add value to the textbox to allow PHP to save it
            jQuery( '#_spk_shortcoders_slug' ).val( FilteredString );

            // show the value and the format to the user
            ShowThisSC( FilteredString, jQuery( '#att_counter' ).val() );

            // show sc div
            SCdivShow();
        }
    }

});

/* ------------------------------------------------
 * Slug - at some point, the user might want to change this
 * --------------------------------------------- */
jQuery( '#_spk_shortcoders_slug' ).on( 'keyup change', function() {
    
    // set sc value
    ShowThisSC( jQuery( this ).val().replace(/[-\s]/g, '_'), jQuery( '#att_counter' ).val() );

    if( jQuery( this ).val().length > 0 ) {
        // show sc div
        SCdivShow();
    } else {
        // hide sc div
        SCdivHide();
    }
});

/* ------------------------------------------------
 * Slug - disable unwanted characters
 * only lower case letters, numbers and underscores allowed
 * --------------------------------------------- */
jQuery( '#_spk_shortcoders_slug' ).bind( 'keypress', function(e) {
    PreventDefaultKeys( e );
});

/* ------------------------------------------------
 * Allow only numeric, lower case letters and underscore
 * --------------------------------------------- */
function PreventDefaultKeys( e ) {
    if( ( e.which < 48 || e.which > 57 && e.which < 97 || e.which > 122) && e.which != 95 ) {
        e.preventDefault();
    }
}

/* ------------------------------------------------
 * Remove special characters and replace spaces with underscore
 * --------------------------------------------- */
function FilterString( s ) {
    return s.replace(/[^a-z0-9\s]/gi, '').replace(/[-\s]/g, '_').toLowerCase();
}

/* ------------------------------------------------
 * Show the shortcode value and the format to the user
 * --------------------------------------------- */
function ShowThisSC( Stringz, AttCount ) {

    // disable interactive display of attributes
    var SC_Attr, SC_FixedAttr;

    // get attributes
    if( AttCount ) {
        for( a=1; a<=AttCount; a++ ) {
            if( jQuery( '#att_name_' + a ).val().length > 0 ) {
                if( SC_Attr === null || SC_Attr === undefined ) {
                    SC_Attr = jQuery( '#att_name_' + a ).val() + '="' + jQuery( '#att_val_' + a ).val() + '"';

                    // remove spaces if only one attribute
                    if( AttCount > 1 ) {
                        SC_Attr += ' ';
                    }
                } else {
                    // concatenate string to the variable
                    SC_Attr += jQuery( '#att_name_' + a ).val() + '="' + jQuery( '#att_val_' + a ).val() + '"';

                    if( a < AttCount ) {
                        SC_Attr += ' ';
                    }
                }
            }
        }
    }

    // show/hide get post by attributes
    if( jQuery( '#gpost_opt' ).is( ':checked' ) ) {
        // show
        SC_FixedAttr = ' id="" slug="" title=""';

        // remove class to show if slug is not empty
        if( jQuery( '#sc_div_gpost_opt' ).hasClass( 'hideme' ) && Stringz ) {
            jQuery( '#sc_div_gpost_opt' ).removeClass( 'hideme' );
        }
    } else {
        SC_FixedAttr = '';

        // add class to hide
        if( !jQuery( '#sc_div_gpost_opt' ).hasClass( 'hideme' ) ) {
            jQuery( '#sc_div_gpost_opt' ).addClass( 'hideme' );
        }
    }

    // remove spaces if no SC_Attr
    if( !SC_Attr ) {
        var SC_Attr_out = '';
    } else {
        var SC_Attr_out = ' ' + SC_Attr;
    }

    // show shortcode
    jQuery( '#this_shortcode' ).html( 'Your shortcode is:<br />' );
    // <strong style="color:#0073aa;">[' + Stringz + SC_Attr_out + ']{@content}[/' + Stringz + ']</strong>
    // set hidden shortcode box value - this will be copied when user clicks on the copy shortcode button
    jQuery( '#this_shortcode_box' ).val( '[' + Stringz + SC_Attr_out + SC_FixedAttr + ']{@content}[/' + Stringz + ']' );

}

/* ------------------------------------------------
 * Listen for Copy Shortcode icon clicks
 * --------------------------------------------- */
jQuery( '#btn_copy_sc' ).click( function() {
    copyToClipboardMsg( document.getElementById("this_shortcode_box"), "msg" );
});

/* ------------------------------------------------
 * Set message for Copy Shortcode icon click
 * --------------------------------------------- */
function copyToClipboardMsg(elem, msgElem) {
    var succeed = copyToClipboard(elem);
    var msg;
    if (!succeed) {
        msg = "Copy not supported or blocked. Press Ctrl+c to copy."
    } else {
        msg = "Shortcode copied."
    }
    if (typeof msgElem === "string") {
        msgElem = document.getElementById(msgElem);
    }
    msgElem.innerHTML = msg;
    setTimeout(function() {
        msgElem.innerHTML = "";
    }, 2000);
}

/* ------------------------------------------------
 * Copy to clipboard function
 * --------------------------------------------- */
function copyToClipboard(elem) {
      // create hidden text element, if it doesn't already exist
    var targetId = "_hiddenCopyText_";
    var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
    var origSelectionStart, origSelectionEnd;
    if (isInput) {
        // can just use the original source element for the selection and copy
        target = elem;
        origSelectionStart = elem.selectionStart;
        origSelectionEnd = elem.selectionEnd;
    } else {
        // must use a temporary form element for the selection and copy
        target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "absolute";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = elem.textContent;
    }
    // select the content
    var currentFocus = document.activeElement;
    target.focus();
    target.setSelectionRange(0, target.value.length);
    
    // copy the selection
    var succeed;
    try {
          succeed = document.execCommand("copy");
    } catch(e) {
        succeed = false;
    }
    // restore original focus
    if (currentFocus && typeof currentFocus.focus === "function") {
        currentFocus.focus();
    }
    
    if (isInput) {
        // restore prior selection
        elem.setSelectionRange(origSelectionStart, origSelectionEnd);
    } else {
        // clear temporary content
        target.textContent = "";
    }
    return succeed;
}

/* ------------------------------------------------
 * Add Attribute
 * --------------------------------------------- */
jQuery( '#add_att' ).on( 'click', function() {
    
    if( jQuery( '#att_counter' ).val().length > 0 ) {
        var AttCounter = parseInt( jQuery( '#att_counter' ).val() ) + 1;
    } else {
        var AttCounter = 1;
    }

    jQuery( '#att_div_' + AttCounter ).fadeIn( FadeSpeed );

    jQuery( '#att_counter' ).val( AttCounter );


});

/* ------------------------------------------------
 * Listen to any anchor clicks
 * --------------------------------------------- */
jQuery("a").click(function(event) {
    // set variables
    var BtnClicked = event.target.id,
        ThisBtnClicked = BtnClicked.split( '_' );
    
    // Remove button for Attributes
    if( ThisBtnClicked[0] == 'rem' ) {
        
        jQuery( '#att_div_' + ThisBtnClicked[2] ).fadeOut( FadeSpeed, function() {
            jQuery( '#att_name_' + ThisBtnClicked[2] ).val('');
            jQuery( '#att_val_' + ThisBtnClicked[2] ).val('');
        });

        var AttCounter = parseInt( jQuery( '#att_counter' ).val() ) - 1;
        jQuery( '#att_counter' ).val( AttCounter );

        ListAttributesDisplayed();
    }

});

/* ------------------------------------------------
 * Listen to any key presses; prevent unwanted characters | ATTRIBUTE NAME
 * --------------------------------------------- */
jQuery( "[id^=att_name]" ).each(function() {

    jQuery( this ).bind( 'keypress', function(e) {
        PreventDefaultKeys( e );
    });

    // disable interactive display of attributes
    jQuery( this ).on( 'keyup change', function() {
        // show shortcode
        ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );
        
        ListAttributesDisplayed();
    });

});

/* ------------------------------------------------
 * List Attributes above the text area
 * --------------------------------------------- */
function ListAttributesDisplayed() {
    
    var AttList, AttCount = jQuery( '#att_counter' ).val(), AttDisplay;

    for( a=1; a<=AttCount; a++ ) {
        
        if( jQuery( '#att_name_' + a ).val().length > 0 ) {

            var AttName = '<a class="onmouseover" id="lattname_' + a + '">{@' + jQuery( '#att_name_' + a ).val() + '}</a>';

            if( AttList === null || AttList === undefined ) {
                AttList = AttName;
            } else {
                // concatenate string to the variable
                AttList += AttName;

            }

            if( a<AttCount ) {
                AttList += ', ';
            }
        }

    }

    if( AttList ) {
        AttDisplay = 'Attribute(s): ' + AttList + '<br />Optional attribute: <a class="onmouseover" id="sc_main_content">{@content}</a>';
    } else {
        AttDisplay = 'Optional attribute: <a class="onmouseover" id="sc_main_content">{@content}</a>';
    }

    jQuery( '#spk_att_list' ).html( AttDisplay );
    
}

/* ------------------------------------------------
 * Listen to any key presses; prevent unwanted characters | ATTRIBUTE VALUE
 * --------------------------------------------- */
jQuery( "[id^=att_val]" ).each(function() {

    jQuery( this ).bind( 'keypress', function(e) {
        PreventDefaultKeys( e );
    });

    jQuery( this ).on( 'keyup change', function() {
        ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );
    });

});

/* ------------------------------------------------
 * Listen for clicks on any of the dynamically added attributes & the content
 * --------------------------------------------- */
jQuery( '#spk_att_list' ).on( 'click', 'a', function() {
    //var SplitBtn = jQuery( this ).attr( 'id' ).split( '_' );

    if( jQuery( this ).attr( 'id' ) == 'sc_main_content' ) {
        var GetThis = '{@content}';
    } else {
        //var GetThis = '{@' + jQuery( '#att_name_' + SplitBtn[1] ).val() + '}'
        var GetThis = jQuery( this ).text();
    }

    insertAtCaret( '_spk_shortcoders_code', GetThis );
});

/* ------------------------------------------------
 * Listen for clicks on the database fields and add to the Display Template
 * --------------------------------------------- */
jQuery( "[id^=db_fields_]" ).each(function() {

    jQuery( this ).on( 'click', function() {

        //var GetTheName = jQuery( this ).attr( 'id' ).split( 'db_fields_' );

        //insertAtCaret( '_spk_shortcoders_code', '{@'+GetTheName[1]+'}' );
        insertAtCaret( '_spk_shortcoders_code', jQuery( this ).text() );

    });
    
});

/* ------------------------------------------------
 * Listen for clicks on the Taxonomy and add to the Display Template
 * --------------------------------------------- */
jQuery( '#puttaxeshere' ).on( 'click', 'a', function() {

    //var GetTheName = jQuery( this ).attr( 'id' ).split( 'db_cfields_' );

    //insertAtCaret( '_spk_shortcoders_code', '{@'+GetTheName[1]+'}' );
    insertAtCaret( '_spk_shortcoders_code', jQuery( this ).text() );
    
});

/* ------------------------------------------------
 * Listen for clicks on the custom fields and add to the Display Template
 * --------------------------------------------- */
jQuery( '#putcustomfieldshere' ).on( 'click', 'a', function() {

    //var GetTheName = jQuery( this ).attr( 'id' ).split( 'db_cfields_' );

    //insertAtCaret( '_spk_shortcoders_code', '{@'+GetTheName[1]+'}' );
    insertAtCaret( '_spk_shortcoders_code', jQuery( this ).text() );
    
});

/* ------------------------------------------------
 * Under construction
 * --------------------------------------------- */
function getSelectedText() {
    var text = "";

    if (typeof window.getSelection != "undefined") {

        text = window.getSelection().toString();

    } else if (typeof document.selection != "undefined" && document.selection.type == "Text") {

        text = document.selection.createRange().text;

    }

    return text;
}

/* ------------------------------------------------
 * Insert at last mouse cursor location
 * --------------------------------------------- */
function insertAtCaret(areaId, text) {
    var txtarea = document.getElementById(areaId);
    if (!txtarea) { return; }

    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
        "ff" : (document.selection ? "ie" : false ) );
    if (br == "ie") {
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    } else if (br == "ff") {
        strPos = txtarea.selectionStart;
    }

    var front = (txtarea.value).substring(0, strPos);
    var back = (txtarea.value).substring(strPos, txtarea.value.length);
    txtarea.value = front + text + back;
    strPos = strPos + text.length;
    if (br == "ie") {
        txtarea.focus();
        var ieRange = document.selection.createRange();
        ieRange.moveStart ('character', -txtarea.value.length);
        ieRange.moveStart ('character', strPos);
        ieRange.moveEnd ('character', 0);
        ieRange.select();
    } else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }

    txtarea.scrollTop = scrollPos;
}

/* ------------------------------------------------
 * Radio - Link Post/Page/CPT
 * --------------------------------------------- */
jQuery( '#ppc_opt' ).click( function() {
    
    if( jQuery( '#ppc_opt' ).is( ":checked" ) ) {
        // show the meta box
        jQuery( '#spk_shortcoders_box_4').removeClass( 'hideme' );
    }

    // show clear button
    ShowHideBtnCPTClear( 'show' );

    // disable post/page/cpt entries dropdown option
    EnableDisableDropDowns( 'enable' );

    // trigger Shortcode display function
    ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );

});

/* ------------------------------------------------
 * Label - Link Post/Page/CPT
 * --------------------------------------------- */
jQuery( '#ppc_opt_text' ).click( function() {

    // check if checkbox is checked - if yes, uncheck
    if( !jQuery( '#ppc_opt' ).is( ":checked" ) ) {
        // select the radio button
        jQuery( '#ppc_opt' ).prop( 'checked', true );

        // show the meta box
        jQuery( '#spk_shortcoders_box_4').removeClass( 'hideme' );
    }

    // show clear button
    ShowHideBtnCPTClear( 'show' );

    // disable post/page/cpt entries dropdown option
    EnableDisableDropDowns( 'enable' );

    // trigger Shortcode display function
    ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );

});

/* ------------------------------------------------
 * Radio - Get Post/Page/CPT
 * --------------------------------------------- */
jQuery( '#gpost_opt' ).on( 'click', function() {
    
    if( jQuery( this ).is(":checked") ) {
        // show the meta box
        jQuery( '#spk_shortcoders_box_4').removeClass( 'hideme' );
    }

    // show clear button
    ShowHideBtnCPTClear( 'show' );

    // disable post/page/cpt entries dropdown option
    EnableDisableDropDowns( 'disable' );

    // trigger Shortcode display function
    ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );

});

/* ------------------------------------------------
 * Label - Get Post/Page/CPT
 * --------------------------------------------- */
jQuery( '#gpost_opt_text' ).on( 'click', function(){

    // check if checkbox is checked - if yes, uncheck
    if( !jQuery( '#gpost_opt' ).is(":checked") ) {
        // select the radio button
        jQuery( '#gpost_opt' ).prop('checked', true);

        // show the meta box
        jQuery( '#spk_shortcoders_box_4').removeClass( 'hideme' );
    }

    // show clear button
    ShowHideBtnCPTClear( 'show' );

    // disable post/page/cpt entries dropdown option
    EnableDisableDropDowns( 'disable' );

    // trigger Shortcode display function
    ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );

});

/* ------------------------------------------------
 * Enable or disable the post/page/cpt entries
 * --------------------------------------------- */
function EnableDisableDropDowns( Aksyon ) {

    jQuery( "[id^=dt_]" ).each(function() {
        if( Aksyon == 'disable' ) {
            jQuery( this ).attr( "disabled", true );
        } else {
            jQuery( this ).removeAttr( "disabled" );
        }
    });

}

/* ------------------------------------------------
 * Clear Button
 * --------------------------------------------- */
jQuery( '#cpt_clear' ).on( 'click', function() {

    // hide this button
    ShowHideBtnCPTClear( 'hide' );

    // clear selected radio buttons
    jQuery( 'input[name="cb_ppc_opt"]' ).each( function() {
        jQuery( this ).prop('checked', false);
    });

    // hide the meta box
    if( !jQuery( '#spk_shortcoders_box_4').hasClass( 'hideme' ) ) {
        jQuery( '#spk_shortcoders_box_4').addClass( 'hideme' );
    }

    // trigger Shortcode display function
    ShowThisSC( jQuery( '#_spk_shortcoders_slug' ).val(), jQuery( '#att_counter' ).val() );

});

/* ------------------------------------------------
 * Post/Page/CPT - Show Clear Button
 * --------------------------------------------- */
function ShowHideBtnCPTClear( Aktion ) {

    if( jQuery( '#cpt_clear' ).hasClass( 'hideme' ) ) {
        if( Aktion == 'show' ) {
            jQuery( '#cpt_clear' ).removeClass( 'hideme' );
        }
    } else {
        if ( Aktion == 'hide' ) {
            jQuery( '#cpt_clear' ).addClass( 'hideme' );
        }
    }

}

/* ------------------------------------------------
 * Label - Instagram
 * --------------------------------------------- */
/*jQuery( '#insta_opt_text' ).on( 'click', function(){

    // check if checkbox is checked - if yes, uncheck
    if( jQuery( '#insta_opt' ).is(":checked") ) {
        jQuery( '#insta_opt' ).prop('checked', false);
    } else {
        jQuery( '#insta_opt' ).prop('checked', true);
    }

});*/

/* ------------------------------------------------
 * Display Post Type Entries
 * --------------------------------------------- */
jQuery( "#dtm_post_type" ).on( "change", function() {

    if( jQuery( "#dtm_post_type" ).val() != 'default_value' ) {

        var PostTypeSelected = jQuery( "#dtm_post_type" ).val().split('#dt_');

        HideOthersBut( '#dt_' + PostTypeSelected[0] );

        if( jQuery( '#db_colnames' ).hasClass( 'hideme' ) ) {
            // show database tables columns for wp_posts
            jQuery( '#db_colnames' ).removeClass( 'hideme' );
        }

    } else {
        HideOthersBut();

        if( !jQuery( '#db_colnames' ).hasClass( 'hideme' ) ) {
            // hide database tables columns for wp_posts
            jQuery( '#db_colnames' ).addClass( 'hideme' );
        }
    }
    
});

/* ------------------------------------------------
 * Hide dropdowns except for what's selected
 * --------------------------------------------- */
function HideOthersBut( ShowThis ) {

    var StopShowing = 0;
    // jQuery( '#gpost_opt' ).is( ':selected' )

    jQuery( "[id^=dt_]" ).each(function() {

        if( jQuery( this ).is( ':visible' ) && jQuery( this ).attr( 'id' ) != ShowThis ) {
            
            jQuery( this ).fadeOut( FadeSpeed, function() {

                // validate if element exists before fade in
                if( jQuery( ShowThis ).length ) {
                    jQuery( ShowThis ).fadeIn( FadeSpeed );
                }

                // show custom fields
                ListCustomFields( jQuery( ShowThis ).val() );

                // show taxonomies
                ListTaxonomies( jQuery( ShowThis ).val() );
            });

            StopShowing++;
        }

    });

    // this will execute if no post has been selected yet
    if( StopShowing == 0 ) {
        // validate if element exists before fade in
        if( jQuery( ShowThis ).length ) {
            jQuery( ShowThis ).fadeIn( FadeSpeed );
        }

        // show custom fields
        ListCustomFields( jQuery( ShowThis ).val() );

        // show taxonomies
        ListTaxonomies( jQuery( ShowThis ).val() );
    }

}

/* ------------------------------------------------
 * Get custom fields on select
 * --------------------------------------------- */
jQuery( "[id^=dt_]" ).each(function() {
    
    jQuery( this ).on( "change", function() {
        // show custom fields
        ListCustomFields( jQuery( this ).val() );

        // show taxonomies
        ListTaxonomies( jQuery( this ).val() );
    });

});

/* ------------------------------------------------
 * Get custom fields
 * --------------------------------------------- */
function ListCustomFields( pid ) {
    jQuery.ajax({
        type: "POST",
        url: FileLoc + "get_custom_fields.php",
        data: 'pid='+pid,
        datatype: "html",
        success: function(result){
            jQuery( '#putcustomfieldshere' ).html( result );
        }
    });
}

/* ------------------------------------------------
 * Get and list taxonomy/taxonomies
 * --------------------------------------------- */
function ListTaxonomies( pid ) {
    jQuery.ajax({
        type: "POST",
        url: FileLoc + "get_my_taxonomies.php",
        data: 'pid='+pid,
        datatype: "html",
        success: function(result){
            jQuery( '#puttaxeshere' ).html( result );
        }
    });
}

/* ------------------------------------------------
 * Check what post type entries are visible and retrieve custom fields
 * --------------------------------------------- */
function CheckForVisibleSelect() {
    
    jQuery( "[id^=dt_]" ).each(function() {
        
        //if( jQuery( this ).is( ':visible' ) ) {
        if( !jQuery( this ).hasClass( 'hideme' ) ) {
            // show custom fields
            ListCustomFields( jQuery( this ).val() );
            
            // show taxonomies
            ListTaxonomies( jQuery( this ).val() );
        }
        
    });

}