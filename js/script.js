var map;

function initApp() {
    getCurrentLocation();
    $(".add-info-dialog-button").on("click", openAddInfoDialog);
    $("#save-add-info-button").on("click", saveDataInfo);
    $("#cancel-add-info-button").on("click", closeAddInfoDialog);
    $("#quick-entry-switch-mobile, #quick-entry-switch-desktop").on("change", toggleQuickEntry);
    $("#concept-switch-mobile, #concept-switch-desktop").on("change", toggleConcept);
    $("#current-location").on("click", getCurrentLocation);
    $("#toggle-map-button").on("click", toggleMap);
    $("#location-fields").on("keydown", "input[type=text]", function () {
        $("#current-location").removeClass("selected");
    });
    $(".entry-card").on("click", toggleItem);
    $.each($(".entry-card"), function (k, v) {
        $(this).attr("data-max-height", $(this).height());
        var maxHeight = 56;
        if (k === 0) maxHeight = $(this).height();
        $(this).css("max-height", maxHeight + "px");
    });
    $('select').material_select();
    $("body").on("click", ".select-wrapper", function () {alert()});
    $(".field-add-button-container").on("contentChange", function () {
        $(this).find(".dropdown-content").css("top", 0);
    });
    if ($(".date-picker").length > 0) initDateTimePicker();
    onResize();
    $(window).on("resize", onResize);
    var content = $("#add-item");
    content.css("max-height", content.height() + parseInt(content.css("padding-top")) + parseInt(content.css("padding-bottom")) + 70);
}

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: {lat: -34.397, lng: 150.644},
        zoom: 8
    });
}

function openAddInfoDialog(e) {
    var button = $(e.currentTarget);
    var dialog = $('#add-data-info-dialog');
    dialog.find("h3").text(button.data("data-info-text") + " toevoegen");
    dialog.find("#add-data-info-type").val(button.data("data-info-type"));
    dialog[0].MaterialDialog.show(true);
    setTimeout(function () {
        dialog.find("input[type=text]").focus();
    }, 50);
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
    var item = "<option value=\"" + id + "\">" + name + "</option>";
    switch (type) {
        case "category":
            $("#category-list").append(item);
            break;
        case "dataType":
            $("#data-type-list").append(item);
            break;
        case "company":
            $("#company-list").append(item);
            break;
    }
    $("select").material_select();
    var content = $("#add-item");
    content.css("max-height", "initial");
    content.css("max-height", content.height() + parseInt(content.css("padding-top")) + parseInt(content.css("padding-bottom")));
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
    var toggle1 = $("#quick-entry-switch-mobile");
    var toggle2 = $("#quick-entry-switch-desktop");
    var toggle = isMobile() ? toggle1 : toggle2;
    var content = $("#add-item");
    if (toggle.prop("checked")) {
        toggle1.prop("checked", true);
        toggle2.prop("checked", true);
        $(".quick-entry").addClass("is-checked");
        content.addClass("quick-entry-mode");
        setTimeout(function () {
            content.addClass("hide-items");
            onResize();
        }, 310);
    } else {
        $(".quick-entry").removeClass("is-checked");
        toggle1.prop("checked", false);
        toggle2.prop("checked", false);
        content.removeClass("hide-items");
        setTimeout(function () {
            content.removeClass("quick-entry-mode");
            setTimeout(function () {
                onResize();
            }, 310);
        }, 10);
    }
}

function toggleConcept() {
    var toggle1 = $("#concept-switch-mobile");
    var toggle2 = $("#concept-switch-desktop");
    var toggle = isMobile() ? toggle1 : toggle2;
    var content = $(".content-section");
    if (toggle.prop("checked")) {
        toggle1.prop("checked", true);
        toggle2.prop("checked", true);
        $(".concept-switch").addClass("is-checked");
        content.addClass("concept-mode");
        content.find(".section-header").addClass("mdl-color--accent").removeClass("mdl-color--primary");
        setTimeout(function () {
            content.addClass("hide-items");
            setTimeout(function () {
                content.addClass("hidden-items");
                onResize();
            }, 310);
        }, 300);
    } else {
        $(".concept-switch").removeClass("is-checked");
        toggle1.prop("checked", false);
        toggle2.prop("checked", false);
        content.removeClass("hidden-items");
        setTimeout(function () {
            content.removeClass("hide-items");
            setTimeout(function () {
                content.removeClass("concept-mode");
                content.find(".section-header").addClass("mdl-color--primary").removeClass("mdl-color--accent");
                setTimeout(function () {
                    onResize();
                }, 310);
            }, 300);
        }, 10);
    }
}

function toggleItem() {
    var item = $(this);
    var currentItem = $(".entry-card.show");
    var content = $(".content-section");
    var maxHeightColl = 56;
    if (item.hasClass("collapsed")) {
        currentItem.removeClass("show").css("max-height", maxHeightColl + "px");
        setTimeout(function () {
            currentItem.addClass("collapsed");
            setTimeout(function () {
                onResize();
            }, 10);
        }, 210);
        item.removeClass("collapsed");
        setTimeout(function () {
            item.addClass("show").css("max-height", item.attr("data-max-height") + "px");
            setTimeout(function () {
                onResize();
            }, 210);
        }, 10);
    } else {
        item.removeClass("show").css("max-height", maxHeightColl + "px");
        setTimeout(function () {
            item.addClass("collapsed");
            setTimeout(function () {
                onResize();
            }, 10);
        }, 210);
    }
}

function toggleMap(action) {
    var button = $("#toggle-map-button");
    if (action == "close" || button.hasClass("selected")) {
        button.removeClass("selected");
    } else {
        button.addClass("selected");
    }

}

function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(setLocation);
        $("#current-location").addClass("selected");
        toggleMap("close");
    }
}

function getCustomLocation(e) {
    setLocation(position);
    $("#current-location").removeClass("selected");
}

function setLocation(position) {
    $("#lat").val(position.coords.latitude).parent().delay(10).addClass("is-focused");
    $("#lng").val(position.coords.longitude).parent().delay(10).addClass("is-focused");
}

function onResize() {
    var content = $(".content-section");
    var max = $(window).height() - $(".mdl-layout__header").height() - 32 - parseInt(content.css("padding-top")) - parseInt(content.css("padding-bottom"));
    if (content.height() > max) content.addClass("static").removeClass("absolute");
    else content.addClass("absolute").removeClass("static");
    $("select").material_select();
}

function isMobile () {
    return $(window).width() < 461;
}
