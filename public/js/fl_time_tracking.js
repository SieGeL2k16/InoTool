var DateTime = luxon.DateTime;
/*
 * Handles calculation of work time from start- and end time.
 * We use luxon.js for all calculations here.
 */
function CalculateTimeDiff()
  {
    var cdstr   = $("#cdate").val();
    var mystart = $("#st").val();
    var myend   = $("#et").val();
    // Proceed only if start/endtime is given, else we use the HH:MM field
    if(mystart === "00:00" || myend === "00:00")
      {
      return false;
      }
    var mystartdate = DateTime.fromISO(cdstr+"T"+mystart);
    var myenddate   = DateTime.fromISO(cdstr+"T"+myend);
    if(myenddate < mystartdate)
      {
      swAlert("Endzeit kann nicht vor der Startzeit liegen!","Achtung!","#et");
      return false;
      }
    var diff  = myenddate.diff(mystartdate,["hours","minutes"]);
    $("#hh").val(diff.toObject()['hours']);
    $("#mm").val(diff.toObject()['minutes']);
    return true;
  }

/**
 * Submits worktime if all is fine.
 * @TODO Integrate bootstrap validation here!
 */
$(".btn_time_save").on('click', function() {
  CalculateTimeDiff();
  var hh = parseInt($("#hh").val());
  var mm = parseInt($("#mm").val());
  var pid= parseInt($("#RefProjectId").val());
  if(!pid)
    {
    swAlert("Bitte ein Projekt auswählen!","Achtung","#RefProjectId");
    return;
    }
  if(!hh && !mm)
    {
    swAlert("Fehlerhafte Zeitangabe!","Achtung","#hh");
    return;
    }
  if($("#description").val() === "")
    {
    swAlert("Es muss eine Beschreibung angegeben werden!","Achtung!","#description");
    return;
    }
  $("#frm_timecollect").submit();
});

/**
 * Removes current entry
 */
$(".btn_time_del").on('click',function(){
  SwalConfirmSubmit("Soll dieser Eintrag wirklich gelöscht werden?","frm_time_del");
});
