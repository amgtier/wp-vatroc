jQuery(document).ready( ($) => {

  if( $( "input.editable-my" ).length ){
    ajax_editable_my( $ );
  }

  if( $( ".autosave" ).length  ) {
    ajax_autosave( $ );
  }

});

function ajax_editable_my( $ ) {
  $( "input.editable-my" ).each( ( key, target_raw ) => {
    const target = $( target_raw );

    // if ( target.hasClass( "autosave" ) ) {
    //   return;
    // }
    
    const field_name = target.attr( 'name' );
    const submit_button = $( "button#submit-" + field_name )[0];
    const form = $( "form#edit-" + field_name )[0];
    
    $( target ).on( "focus", (event)=>{
      $( submit_button ).attr( 'hidden', false );
    });

    submit_form( $, target, field_name, submit_button, form, () => {
        target.removeClass( "ajax-danger" );
        $( submit_button ).attr( 'hidden', true );
    } );

  });
}

var autosave_timeout;
function ajax_autosave( $ ) {
  $( "input.editable-my.autosave" ).each( (key, target_raw ) => {
    const target = $( target_raw );
    const field_name = target.attr( 'name' );
    const form = $( "form#edit-" + field_name )[0];

    $( target ).on( "keyup", ( event ) => {      
      target.addClass( "ajax-danger" );
      clearTimeout( autosave_timeout );
      autosave_timeout = setTimeout( () => {
        $( form ).trigger( 'submit' );
      }, 500 );
    })   
  } );
}

function submit_form( $, target, field_name, submit_button, form, callback ) {
  $( form ).submit(( event )=> {
    event.preventDefault();

    const data = {
      action: 'vatroc_my_editable_field',
      field_name: field_name,
      value: $( target ).val(),
    }

    $.post( ajax_object.ajax_url, data ).done(
      () => {
        if ( callback ) {
          callback();
        }
      }
    );
  });
}