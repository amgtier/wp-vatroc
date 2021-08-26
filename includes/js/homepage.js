jQuery(document).ready( ($) => {
    if ( $( "#vatroc-homepage-atc" ).length ) {
        ajax_shortcode_atc( $ );
    }
    if ( $( "#vatroc-homepage-atc" ).length ) {
        ajax_shortcode_metar( $, [ "RCTP", "RCKH", "RCSS" ] );
    }
});

function ajax_shortcode_atc( $ ) {
    var atc_list = $( "<div>" );

    var data = {
        'action': 'vatroc_homepage_atc',
    };

    $.post( ajax_object.ajax_url, data, 
        res => {
            atc_list.html( res );
        });

    $( "#vatroc-homepage-atc" ).html( atc_list );

}

function ajax_shortcode_metar( $, icaos ) {
    if ( !icaos ) { return; }

    var metar = $( "<div>" );
    var str_icaos = "";
    $.each( icaos, (idx, val) => {
        if ( idx > 0 ) {
            str_icaos += "," + val;
        } else {
            str_icaos += val;
        }
    });

    var data = {
        'action': 'vatroc_homepage_metar',
        'icaos': str_icaos
    };

    $.post( ajax_object.ajax_url, data, 
        res => {
            metar.html( res );
        });

    $( "#vatroc-homepage-metar" ).html( metar );

}
