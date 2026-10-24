/** Customer fields filled from data attributes of selected customer option */
const customerFields = {
  number:   '#customerNumber',
  name:     '#name',
  street:   '#street',
  postal:   '#postalCode',
  city:     '#city',
  country:  '#country',
};

/**
 * Parses amount with comma or dot as decimal separator
 * @param value
 * @returns {number} NaN if invalid
 */
function parseAmount(value)
  {
  value = String(value).trim().replace(',', '.');
  if(value === '' || isNaN(value))
    {
    return NaN;
    }
  return parseFloat(value);
  }

/**
 * Formats amount with 2 decimals and comma as separator
 * @param value
 * @returns {string}
 */
function formatAmount(value)
  {
  return (Math.round(value * 100) / 100).toFixed(2).replace('.', ',');
  }

/**
 * Returns factor netto -> brutto
 * @returns {number} NaN if tax is invalid
 */
function getTaxFactor()
  {
  if($('#inv_notax').is(':checked'))
    {
    return 1;
    }
  return 1 + parseAmount($('#inv_tax').val()) / 100;
  }

/** Calculates brutto from netto */
function calcBrutto()
  {
  let netto  = parseAmount($('#inv_netto').val());
  let factor = getTaxFactor();
  if(isNaN(netto) || isNaN(factor))
    {
    return;
    }
  $('#inv_brutto').val(formatAmount(netto * factor));
  }

/** Calculates netto from brutto */
function calcNetto()
  {
  let brutto = parseAmount($('#inv_brutto').val());
  let factor = getTaxFactor();
  if(isNaN(brutto) || isNaN(factor) || factor === 0)
    {
    return;
    }
  $('#inv_netto').val(formatAmount(brutto / factor));
  }

$(document).ready(function() {
  $('#inv_tax').prop('readonly', $('#inv_notax').is(':checked'));
});

/** Copy customer data into form */
$('#RefCustomerId').on('change', function() {
  let opt = $(this).find('option:selected');
  $.each(customerFields, function(key, selector) {
    $(selector).val(opt.attr('data-' + key) || '');
  });
});

$('#inv_netto').on('input', calcBrutto);
$('#inv_brutto').on('input', calcNetto);
$('#inv_tax').on('input', calcBrutto);

/** Normalize amount display after editing */
$('#inv_netto, #inv_brutto').on('change', function() {
  let value = parseAmount($(this).val());
  if(!isNaN(value))
    {
    $(this).val(formatAmount(value));
    }
});

$('#inv_notax').on('change', function() {
  $('#inv_tax').prop('readonly', this.checked);
  calcBrutto();
});

$('#btn_invoice_delete').on('click', function() {
  SwalConfirmSubmit("Diese Rechnung wirklich löschen?<br>Die hinterlegte PDF-Datei wird ebenfalls gelöscht!",'frm_invoice_delete');
});

/** Mark PDF for removal on save */
$('#btn_pdf_delete').on('click', function() {
  $('#inv_pdf_delete').val(1);
  $('#inv_pdf_buttons').addClass('d-none');
  $('#inv_pdf_deleted').removeClass('d-none');
});
