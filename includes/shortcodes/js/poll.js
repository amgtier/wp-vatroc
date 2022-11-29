jQuery(document).ready( ($) => {
  if ( $( "button.res" ).length ) {
    ajax_button_res( $ );
  }
});

function ajax_button_res( $ ) {
  $( "button.res" ).on("click", (event)=>{
    const target = $($(event)[0].target);

    const data = {
      action: 'vatroc_poll_vote',
      name: target.attr('name'),
      value: target.attr('value'),
      id: ajax_object.page_id
    }

    $.post( ajax_object.ajax_url, data, 
      res => {
        const obj = JSON.parse(res);
        $("button.res[name='" + obj.name + "']").each((idx, btn)=>{
          $(btn).removeClass( "active" );
        });
        const btn = $("button.res[name='" + data.name + "'][value='" + data.value + "']")
        $(btn[0]).addClass( "active" );

        console.log(obj.value);
        $(".result[data-option='" + obj.name + "'] [data-value='accept']").html(
          obj.value['accept'].join("")
        );
        $(".result[data-option='" + obj.name + "'] [data-value='tentative']").html(
          obj.value['tentative'].join("")
        );
        $(".result[data-option='" + obj.name + "'] [data-value='reject']").html(
          obj.value['reject'].join("")
        );
      });
  });
}