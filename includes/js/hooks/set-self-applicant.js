jQuery(document).ready( ($) => {
    if( $( "#set-self-applicant" ).length ){
        ajax_set_self_applicant( $ );
    }
});

function ajax_set_self_applicant( $ ){
    $( "#set-self-applicant" ).on( "click", ( event )=>{
        event.preventDefault();
        const data = {
            action: 'vatroc_set_self_applicant',
        };

        $.post( ajax_object.ajax_url, data )
        .done( () => {
            $(event.target).removeClass();
            $(event.target).addClass( 'btn-success' );
            $(event.target).attr( 'disabled', true );
        })
        .fail( () => {
            $(event.target).removeClass();
            $(event.target).addClass( 'btn-danger' );
        });
    });
}