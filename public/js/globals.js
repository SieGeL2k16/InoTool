/** Global javascript */

/**
 * Confirm dialog which submits a given form id
 * @param confirm_text
 * @param form_id
 */
function SwalConfirmSubmit(confirm_text, form_id)
  {
  Swal.fire({
    title: 'Achtung!',
    html: confirm_text,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: '<i class="fa-solid fa-trash"></i> Löschen',
      cancelButtonText: '<i class="fa-solid fa-xmark"></i> Abbruch',
    }).then((result) => {
      if (result.isConfirmed) {
        $("#"+form_id).submit();
      }
    })
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

/**
 * Returns locale-aware currency string for Euro and colorizes red for minus and green for plus values
 * @param value
 * @param use_html
 * @param locale Defaults to germany
 * @returns {string}
 * @constructor
 */
function FormatEuro(value,use_html = false, locale='de-DE')
  {
  let currencystr =  new Intl.NumberFormat(locale, {
      style: 'currency',
      currency: 'EUR'
    }).format(value);
  if(use_html === false)
    {
    return currencystr;
    }
  let cl = "";
  if(value < 0)
    {
    cl = 'text-danger';
    }
  else
    {
    cl = 'text-success';
    }
  return '<span class="'+cl+'">'+currencystr+"</span>";
  }

/**
 * Taken from Stackoverflow: https://stackoverflow.com/a/3605602/3874598
 * @param len
 * @returns {string}
 */
Number.prototype.padZero= function(len){
  var s= String(this), c= '0';
  len= len || 2;
  while(s.length < len) s= c + s;
  return s;
}

/**
 * Handle Ajax error (write to console + use toast)
 * @param textStatus
 * @param errorThrown
 * @constructor
 */
function ajaxError(textStatus,errorThrown)
  {
  console.log("STATUS: "+textStatus+"\nERROR: "+errorThrown);
  $.toast({
    text:errorThrown,
    position : 'bottom-right',
    'icon': 'error',
    'heading': textStatus,
  });
  }

/**
 * Replacement for standard javascript "alert" box
 * @param alerttext
 * @param title Optional a different title (default is "Info!")
 * @param focus Selector recieving focus once the modal is closed (optional)
 */
function swAlert(alerttext, title, focus="")
  {
  if(title === undefined || title === "")
    {
    title = "Info!";
    }
  Swal.fire({
    title: title,
    html: alerttext,
    icon: "warning",
    customClass: {
      confirmButton: 'btn btn-primary',
    },
    confirmButtonText: 'Okay',
    buttonsStyling: false,
    didClose: () => {
      if(focus !== "")
        {
        setTimeout(() => $(focus).focus(), 100);
        }
      }
    });
  }

/**
 * Converts a YYYYMM string to readable and localized string
 * @param yyyymm
 */
function convertYYYYMMToText(yyyymm)
  {
  var DateTime = luxon.DateTime;
  var dt = DateTime.fromISO(yyyymm+"01");
  return dt.toLocaleString({ month: 'long', year: 'numeric' });
  }



/**
 * Shows FLProjectEntries for a given year/month combo
 * URL is saved in InoBase.html.twig
 * @param yyyymm
 */
function fl_showEntriesFromMonth(yyyymm)
  {
  $.ajax($("#globalModal").data('entriesyyyymm'), {
    'method':'post',
    'dataType': 'json',
    'data': {'YM':yyyymm}
    }).done(function(json) {
      $("#globalModalLabel").html("Zeige Einträge für "+convertYYYYMMToText(yyyymm));
      $("#globalModalBody").html(json['HTML']);
      let myModal = new bootstrap.Modal(document.getElementById('globalModal'));
      myModal.show();
    }).fail(function(jqxhr,textStatus,errorThrown) {
      ajaxError(textStatus,errorThrown);
    });
  }

/**
 * Configure javascript components select2
 */
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

  /** Setup select2 defaults */
  $.fn.select2.defaults.set("theme","bootstrap-5");
  $.fn.select2.defaults.set("language", "de");

});

/** Set default values for DataTables */
$.extend( $.fn.dataTable.defaults, {
  "stateSave": true,      // Save filter settings
  "stateDuration": 0,     // save forever
  "language": {
    "url": JS_DIR+"/vendor/datatables."+APP_LANG+".json",
  },
});

// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
  .forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault()
        event.stopPropagation()
      }
      form.classList.add('was-validated')
    }, false)
  })
})()
