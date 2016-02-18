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
    dialog.find("#add-data-info-type").val(button.data("data-inf-type"));
    dialog[0].MaterialDialog.show(true);
}

function saveDataInfo() {
    var name = $("#add-data-info").val();
    var type = $("#add-data-info-type").val();
    $.ajax({
        data: {
            name: name,
            function: "create",
            type: type
        },
        url: "includes/dataInfo.php",
        method: "POST"
    }).success(function (output) {
        console.log(output);
    })
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
