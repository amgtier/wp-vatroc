jQuery(document).ready( ($) => {
  if( $( ".autosave" ).length ){
    ajax_save_form( $ );
  }

  if( $( "form.vatroc-form" ).length ){
    ajax_submit_form( $ );
  }

  if( $( ".toggle-value" ).length ) {
    toggle_value_handler( $ );
  }

  if( $( ".option" ).length ) {
    option_handler( $ );
  }

  checkbox_set_boolean( $ );
});

var autosave_timeout;
function ajax_save_form( $ ){
  $( ".autosave" ).each( (key, target_raw) => {
    const target = $( target_raw );
    const form = target.parents("form");

    $( target ).on( "change keyup", ( event ) => {
      target.addClass( "ajax-danger" );
      clearTimeout( autosave_timeout );
      console.log(form.serialize());
      autosave_timeout = setTimeout( () => {
        save_draft($, form.serialize(), () => {
          target.removeClass( "ajax-danger" );
        });
      }, 500)
    });
  });
}

function ajax_submit_form( $ ){
  $( "form.vatroc-form" ).each( (key, target) => {
    const form = $( target );
    form.submit( event => {
      event.preventDefault();

      const data = {
        action: 'vatroc_form_submit',
        id: ajax_object.page_id,
        data: form.serialize(),
        no_delete: true,
      };

      $.post( ajax_object.ajax_url, data,
        () => {
          form.addClass( "hidden" );
          $( "div.form-submit-message" ).removeClass( "hidden" );
        });

      // &.post

    });
  } )
}

function save_draft( $, serialized, callback ) {
  const data = {
    action: 'vatroc_form_save_draft',
    id: ajax_object.page_id,
    data: serialized,
  };

  $.post( ajax_object.ajax_url, data,
    () => {
      callback();
    });
}

function checkbox_set_boolean( $ ) {
  $( "input[type=checkbox]" ).on( "change", function() {
    const name = $( this ).attr( "name" );
    if( $( this ).is( ":checked" ) ) {
      $( 'input[name= "' + name + '"]' ).val( true );
        $( this ).val( true );
        $( '.toggle-value[data-name=' + name + ']' ).html( "Yes" );
    }
    else{
       $( this ).val( false );
       $( 'input[name= "' + name +'"]' ).val( false );
       $( '.toggle-value[data-name=' + name + ']' ).html( "No" );
      }
  });
}

function toggle_value_handler( $ ) {
  $( '.toggle-value' ).each( (key, target) => {
    const name = $(target).data('name');
    if (name){
      const input = $($("input[name=" + name + "]")[1]);
      if(input.prop('checked')){
        $(target).html("Yes");
      } else {
        $(target).html("No");
      }
    }
  });
}

function option_handler( $ ) {
    $( '.option' ).each( (key, target) => {
        $( target ).on( "click", ( event ) => {

        const name = $(target).data( 'name' );
        let optionValue = $( 'input[name=' + name + ']' ).val().split( "," );
        const currKey = (key + 1 ).toString();
        const value = $(target).val();

            console.log(value);
        if( value === 'on' || value === 'true' ){
            if(!optionValue.includes( currKey )){
                optionValue.push( currKey );
            }
        } else {
            optionValue = optionValue.filter( item => item !== currKey );
        }
        console.log(optionValue)
        });
    } );
}
