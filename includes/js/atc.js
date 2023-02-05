jQuery(document).ready( ($) => {
  console.log(ajax_object);
    if( $( ".sess-set-as" ).length ){
      ajax_sess_set_as( $ );
    }
  });
  
  function ajax_sess_set_as( $ ){
    $( "select.sess-set-as" ).on( "change", ( event )=>{
        const data = {
            action: 'vatroc_my_set_atc_date_from_sess',
            user: $(event.target).data( "user" ),
            date: $(event.target).data( "date" ),
            key: $(event.target).val(),
        };
  
          $.post( ajax_object.ajax_url, data,
            res=>{
              location.reload();
            })
    });
  }