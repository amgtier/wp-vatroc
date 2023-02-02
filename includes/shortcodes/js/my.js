jQuery(document).ready( ($) => {
  if( $( "input#nickname" ).length ){
    ajax_edit_nickname( $ );
  }
});

function ajax_edit_nickname( $ ){
  const input = $( "input#nickname" )[0];
  const submit_button = $( "button#submit-nickname" )[0];
  const form = $( "form#edit-nickname" )[0];
  
  $(input).on( "focus", (event)=>{
    $(submit_button).attr( 'hidden', false );
  });

  $(form).submit((event)=> {
    event.preventDefault();

    const data = {
      action: 'vatroc_my_set_nickname',
      nickname: $(input).val(),
    }

    $.post( ajax_object.ajax_url, data,
      res=>{
        $(submit_button).attr( 'hidden', true );
        location.reload();
      })
  });
}