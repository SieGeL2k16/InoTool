$(document).ready(function() {
  $('.ino-select2').select2({});

  /** Handle category changes */
  $(".catchanger").on('click', function() {
    $("#accid").val($(this).data('id'));
    document.getElementById('newcat').value=parseInt($(this).data('catid'));
    let myModal = new bootstrap.Modal(document.getElementById('modal_catchange'));
    myModal.show();
  });

  /** Handle click on description */
  $(".details").on('click', function() {
    window.location.href=$(this).data('url');
  });

  /** Handle checkboxes */
  $(".chk_delete").on('click', function() {
    if($(".chk_delete:checked").length)
      {
        $("#btn_delete").prop('disabled', false);
      }
    else
      {
        $("#btn_delete").prop('disabled', true);
      }
  });

  /** Handle removal */
  $("#btn_delete").on('click', function() {
    // frm_acc_list_table
    SwalConfirmSubmit("Wirklich "+$(".chk_delete:checked").length+" Zeile(n) löschen?",'frm_acc_list_table');
  });

  /** Handle click on print (PDF) button */
  $("#btn_pdf").on('click', function() {
    $("#pdf_cat").val($("#f_cat").val());
    $("#pdf_month").val($("#f_month").val());
    $("#pdf_year").val($("#f_year").val());
    $("#pdf_search").val($("#f_srch").val());
    $("#frm_pdf").submit();
  });

});
