jQuery(document).ready( ($) => {

    function chart_box_handler() {
        $( ".chart-box" ).draggable();
        $( ".chart-box" ).resizable();
        $( ".close-chart" ).on( "click", (e) => {
            var ele = $(e.target);
            ele.parent().remove();
        });
        $( ".chart-move-up" ).on( "click", (e) => {
            var ele = $(e.target).parent();
            ele.css( "z-index", parseInt( ele.css( 'z-index' ) ) + 1 );
        });
        $( ".chart-move-down" ).on( "click", (e) => {
            var ele = $(e.target).parent();
            ele.css( "z-index", parseInt( ele.css( 'z-index' ) ) - 1 );
        });
    }
    
    function main() {
        chart_box_handler();
        $( ".toggle-charts" ).selectmenu({
            select: (e, ui) => {
                $( "#" + ui.item.element.parent().attr( "id" ) + "-button .ui-selectmenu-text" ).html( ui.item.element.siblings( "option:first" ).text() );
                var new_chart = $( "<div>", { "class": "postbox-container magic-charts chart-box ui-widget-header", style: "position: absolute; border: 1px dashed #000; overflow: auto; z-index: 9991;" } );
                new_chart.append( $( "<span>", { text: "X", style: "cursor: pointer; position: absolute; right: 0;", "class": "close-chart" } ) );
                new_chart.append( $( "<span>", { text: "^", style: "cursor: pointer; position: absolute; right: 20px;", "class": "chart-move-up" } ) );
                new_chart.append( $( "<span>", { text: "v", style: "cursor: pointer; position: absolute; right: 40px;", "class": "chart-move-down" } ) );
                new_chart.append( $( "<h1>", { text: "Chart" } ) );
                new_chart.append( $( "<img>", { style: "width: 100%", src: ui.item.element.data( "target" ) } ) );
                $( "#dashboard-widgets" ).append( new_chart );
                chart_box_handler();
            }
        });
    }
    main();
});

