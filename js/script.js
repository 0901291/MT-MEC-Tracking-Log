function initApp() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            $("#lat").val(position.coords.latitude).parent().addClass("is-focused");
            $("#lng").val(position.coords.longitude).parent().addClass("is-focused");
        });
    }
    $(".add-info-dialog-button").on("click", openAddInfoDialog);
    $("#save-add-info-button").on("click", saveDataInfo);
    $("#cancel-add-info-button").on("click", closeAddInfoDialog);
    $("#quick-entry-switch").on("change", toggleQuickEntry);
    initDateTimePicker();
    positionResize();
    $(window).on("resize", positionResize);
    var content = $("#add-item");
    content.css("max-height", content.height() + parseInt(content.css("padding-top")) + parseInt(content.css("padding-bottom")));
}

function openAddInfoDialog(e) {
    var button = $(e.currentTarget);
    var dialog = $('#add-data-info-dialog');
    dialog.find("h3").text(button.data("data-info-text") + " toevoegen");
    dialog.find("#add-data-info-type").val(button.data("data-info-type"));
    console.log(button.data("data-info-type"));
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
        method: "POST",
        success: function (id) {
            addDataInfoToList(type, id, name);
            closeAddInfoDialog();
        }
    });
}

function addDataInfoToList(type, id, name) {
    switch (type) {
        case "category":
            var item = "<li class=\"mdl-menu__item category-item\">" +
                            "<input id=\"category-" + id + "\" value=\"" + id + "\" name=\"category\" type=\"radio\">" +
                            "<label for=\"category-" + id + "\">" + name + "</label>" +
                        "</li>";
            $("#category-list").append(item);
            break;
        case "dataType":
            var item = "<label class=\"mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect\" for=\"datatype-" + id + "\">" +
                            "<input type=\"checkbox\" id=\"datatype-" + id + "\" name=\"data-types[]\" class=\"mdl-checkbox__input\" value=\"" + id + "\">" +
                            "<span class=\"mdl-checkbox__label\">" + name + "</span>" +
                        "</label>";
            $("#data-type-list").append(item);
            break;
        case "company":
            var item = "<label class=\"mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect\" for=\"company-" + id + "\">" +
                            "<input type=\"checkbox\" id=\"company-" + id + "\" name=\"companies[]\" class=\"mdl-checkbox__input\" value=\"" + id + "\">" +
                            "<span class=\"mdl-checkbox__label\">" + name + "</span>" +
                        "</label>";
            $("#company-list").append(item);
            break;
    }
    componentHandler.upgradeAllRegistered();
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

function toggleQuickEntry() {
    var toggle = $("#quick-entry-switch");
    var content = $("#add-item");
    if (toggle.prop("checked")) {
        content.addClass("quick-entry-mode");
        setTimeout(function () {
            content.addClass("hide-items");
            positionResize();
        }, 300);
    } else {
        content.removeClass("hide-items");
        setTimeout(function () {
            content.removeClass("quick-entry-mode");
            setTimeout(function () {
                positionResize();
            }, 300);
        }, 10);
    }
}

function positionResize() {
    var content = $("#add-item");
    var max = $(window).height() - $(".mdl-layout__header").height() - 32 - parseInt(content.css("padding-top")) - parseInt(content.css("padding-bottom"));
    console.log(max);
    console.log(content.height());
    if (content.height() > max) content.addClass("static").removeClass("absolute");
    else content.addClass("absolute").removeClass("static");
}
