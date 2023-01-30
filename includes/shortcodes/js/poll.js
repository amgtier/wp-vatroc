jQuery(document).ready( ($) => {
  if ( $( "button.res" ).length ) {
    ajax_button_res( $ );
  }

  if ( $( ".hide-option" ).length ) {
    ajax_toggle_hide_option( $ );
  }

  if ( $( ".option-description" ).length ) {
    ajax_update_option_description( $ );
  }

  if ( $( "input#create-option" ).length ) {
    ajax_create_option( $ );
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


function ajax_toggle_hide_option( $ ){
  $( ".hide-option" ).on( "click", (event) => {
    event.preventDefault();
    const target = $($(event)[0].target);

    const data = {
      action: 'vatroc_poll_toggle_hide',
      name: target.data('name'),
      id: ajax_object.page_id
    }

    $.post( ajax_object.ajax_url, data, 
      res => {
        $(target).html( res.hidden ? "unhide" : "hide" );
      }
    );
  })
}

var option_desc_timeout;
function ajax_update_option_description( $ ){
  console.log("hooked2");
  $( ".option-description" ).on( "keyup", (event) => {
    const target = $($(event)[0].target);
    target.addClass( "ajax-danger" );
    clearTimeout( option_desc_timeout );
    option_desc_timeout = setTimeout(()=>{
      const data = {
        action: 'vatroc_poll_update_description',
        name: target.data('name'),
        id: ajax_object.page_id,
        value: target.val(),
      }

      $.post( ajax_object.ajax_url, data, 
        res => {
          target.removeClass( "ajax-danger" );
        }
      );
    }, 500);
})
}


function ajax_create_option( $ ){
  const input = $( "input#create-option" )[0];
  const submit_button = $( "button#submit-option" )[0];
  const form = $( "form#create-option" )[0];
  
  $(input).on( "focus", ( event )=>{
    $(submit_button).attr('hidden', false);
  });

  $(form).submit((event)=> {
    event.preventDefault();

    const data = {
      action: 'vatroc_poll_create_option',
      id: ajax_object.page_id,
      name: $(input).val(),
      type: 'date',
    }

    $.post( ajax_object.ajax_url, data,
      res=>{
        $(submit_button).attr( 'hidden', true );
        location.reload();
      })
  });
}