var timeouts = {};

jQuery(document).ready( ($) => {
    $( ".postbox-wrapper" ).sortable();
    $( ".timer-box" ).removeClass( "disabled" );
    $( ".timer-box" ).on( "click", (e) => {
        var box = $(e.target);
        var sec = box.data( "sec" ) - 1;
        if ( box.attr( "status") !== "busy" ) {
            box.attr( "status", "busy" );
            countdown( sec, box );
        } else {
            box.attr( "status", "" );
            clearTimeout( timeouts[ box.index() ] );
            box.html( Math.floor( ( sec + 1 ) / 60 ) + " Min" );
            if ( (sec + 1) % 60 != 0) {
                box.html( box.html() + ( ( sec + 1 ) % 60 ).toString().padStart(2, '0') + " Sec" );
            }
        }
    });
});

function countdown(sec, box) {
    box.html( Math.floor( sec / 60 ) + ":" + ( sec % 60 ).toString().padStart(2, '0') );
    if (sec > 0) {
        timeouts[ box.index() ] = setTimeout( () => {
            countdown(sec - 1, box);
        }, 1000 );
    }
}
