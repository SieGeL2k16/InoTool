/** Global javascript */

/**
 * Asks for confirmation when deleting.
 * @param confirm_url URL to process if user confirms removal.
 */
function AskForDelete(confirm_url, add_text)
  {
  if(add_text === "")
    {
    add_text = "Das Löschen kann nicht rückgängig gemacht werden!";
    }
  Swal.fire({
    title: 'Wirklich löschen?',
    html: add_text,
    type: 'warning',
    showCancelButton: true,
    cancelButtonColor: '#3085d6',
    confirmButtonColor: '#d33',
    confirmButtonText: '<i class="bx bx-trash"></i> Löschen',
    cancelButtonClass: 'btn btn-primary ml-2',
    confirmButtonClass: 'btn btn-danger',
    cancelButtonText: '<i class="bx bx-window-close"></i> Abbruch',
    buttonsStyling: false,
    }).then(function (result) {
    if (result.value) {
      window.location.href=confirm_url;
      }
    });
  }

/**
 *
 * @param urlToSend
 */
function downloadFile(urlToSend,id_to_disable)
  {
  var id = "#"+id_to_disable;
  var oh = $(id).html();    // Save old value
  $(id).html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled',true);
  //$("#"+id_to_disable).addClass('spinner-border').prop('disabled',true);
  var req = new XMLHttpRequest();
  req.open("GET", urlToSend, true);
  req.responseType = "blob";
  req.onload = function (event) {
    var blob = req.response;
    var fileName = req.getResponseHeader("fileName") //if you have the fileName header available
    var link=document.createElement('a');
    link.href=window.URL.createObjectURL(blob);
    link.download=fileName;
    //$("#"+id_to_disable).removeClass('spinner-border').prop('disabled',false);
    $(id).html(oh).prop('disabled',false);
    link.click();
    };
  req.send();
  }


$(document).ready(function () {
  $.validator.setDefaults({
    errorElement: "em",
    errorPlacement: function (error, element) {
      // Add the `invalid-feedback` class to the error element
      error.addClass("invalid-feedback");
      if (element.prop("type") === "checkbox") {
        error.insertAfter(element.next("label"));
      } else {
        error.insertAfter(element);
      }
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).addClass("is-valid").removeClass("is-invalid");
    }
  });

  /** Activate tooltips */
  $(function () {
    $('[data-toggle="tooltip"]').tooltip({ boundary: 'window' });
  });

  /** Add click handler to all alert history tables */
  $(document).ready(function() {
    $("body").on('click','.tbl_ah_list tbody td',function() {
      window.location.href=$(this).data('url');
    });
  });

  /** Setup select2 defaults */
  $.fn.select2.defaults.set("theme","bootstrap4");
  $.fn.select2.defaults.set("language", "de");

});
