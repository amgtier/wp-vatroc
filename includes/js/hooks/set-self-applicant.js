jQuery(document).ready( ($) => {
    if( $( "#set-self-applicant" ).length ){
        console.log("Hihi")
        ajax_set_self_applicant( $ );
    }
});

function ajax_set_self_applicant( $ ){
    $( "#set-self-applicant" ).on( "click", ( event )=>{
        event.preventDefault();
        const data = {
            action: 'vatroc_set_self_applicant',
        };

        $.post( ajax_object.ajax_url, data,
            res=>{
                console.log(res);
        });
    });
}