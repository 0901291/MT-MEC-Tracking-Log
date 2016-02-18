function initApp() {
    $(".add-info-dialog-button").on("click", openAddInfoDialog);
    $("#save-add-info-button").on("click", saveDataInfo);
    $("#cancel-add-info-button").on("click", closeAddInfoDialog);
    initDateTimePicker();
}

function openAddInfoDialog(e) {
    var button = $(e.currentTarget);
    var dialog = $('#add-data-info-dialog');
    dialog.find("h3").text(button.data("data-info-text") + " toevoegen");
    dialog[0].MaterialDialog.show(true);
}

function saveDataInfo() {
    var name = $("#add-data-info").val();
    // ajax
    closeAddInfoDialog();
}

function closeAddInfoDialog() {
    var dialog = $('#add-data-info-dialog');
    dialog[0].MaterialDialog.close();
    $("#add-data-info").val("");
}

function initDateTimePicker() {
    $("#date").bootstrapMaterialDatePicker({
        weekStart: 0,
        format: "DD/MM/YYYY",
        time: false,
        lang: "nl",
        currentDate: moment(new Date())
    });
    $("#time").bootstrapMaterialDatePicker({
        weekStart: 0,
        format: "H:mm",
        date: false,
        lang: "nl",
        currentDate: moment(new Date())
    });
}
