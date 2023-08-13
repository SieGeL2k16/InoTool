var dtable;
function loadData()
  {
    dtable = $("#tbl_fl_invoices").DataTable({
      ajax: {
        type: "POST",
        url: ajaxUrl,
        data: function (d)
          {
            d.F_TYPE = $('#f_type').val();
            d.F_PAID = $('#f_paid').val();
          }
      },
      columnDefs: [
        {className: "td_edit align-middle", targets: [0,1,2,3,4,5,6]},
        {className: "text-end", targets: [5]},
        {
          className: "text-center",
          targets: [6],
        },
        {
          targets: [7],
          className: "text-center",
          "render": function ( data, type, row, meta )
            {
              //console.log(row);
              return type === 'display' ?
              '<button type="button" class="btn btn-sm btn-secondary dl_invoice" data-id="'+row[0]+'" title="Rechnung &quot;'+data+'&quot; downloaden"><i class="fa-solid fa-download"></i></button>' :
              data;
            }
        },
      ],
      "processing": true,
      'serverSide': true,
      "stateSave": true,
      "stateDuration": 0,
      "fnInitComplete": function() {this.fnAdjustColumnSizing(true);},
    });
  }
$(document).ready(function() {
  loadData();
});
// Handle click on table entry
$('#tbl_fl_invoices tbody').on('click', '.td_edit', function () {
  var data = dtable.cell(this).data();
  if (Array.isArray(data) === true)
    {
    return;
    }
  var row = dtable.row(dtable.cell(this).index().row).data();
  window.location.href=formurl+"/"+row[0];
});

/** Handle download of invoice */
$("body").on('click', ".dl_invoice",function() {
  window.location.href=blobUrl+"/"+$(this).data('id');
});

/** React on filter selections */
$(".fl_filter").on('change', function() {
  dtable.ajax.reload(null,false);
});
