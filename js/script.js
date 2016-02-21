var map;
var marker;
var googleUser = {};

function initApp() {
    if ($("#google-login-button").length > 0) loadGoogleLogin();
    $(".add-info-dialog-button").on("click", openAddInfoDialog);
    $("#add-data-info").on("keydown", keyDownNewDataInfo);
    $("#save-add-info-button").on("click", saveDataInfo);
    $("#cancel-add-info-button").on("click", closeAddInfoDialog);
    $("#quick-entry-switch-mobile, #quick-entry-switch-desktop").on("change", toggleQuickEntry);
    $("#concept-switch-mobile, #concept-switch-desktop").on("change", toggleConcept);
    $("#current-location").on("click", getCurrentLocation);
    $("#toggle-map-button").on("click", toggleMap);
    $("#location-fields").on("keydown", "input[type=text]", function () {
        $("#current-location").removeClass("selected");
    });
    $(".entry-remove").on("click", confirmDelete);
    $("#logout").on("click", logout);
    $(".entry-card-header").on("click", toggleItem);
    $(".field-add-button-container").on("contentChange", function () {
        $(this).find(".dropdown-content").css("top", 0);
    });
    $(window).on("resize", onResize);
    resizeContent();
    if ($("#map").length > 0) {
        initMap();
        if ($("body").hasClass("edit-mode")) {
            setLocation({
                lat: parseFloat($("#lat").val()),
                lng: parseFloat($("#lng").val())
            });
        } else {
            getCurrentLocation();
        }
    }
    if ($("select").length > 0) $('select').material_select();
    setTimeout(function () {
        $.each($(".entry-card"), function (k, v) {
            $(this).attr("data-max-height", $(this).height());
            var maxHeight = 56;
            if (k === 0) maxHeight = $(this).height();
            $(this).css("max-height", maxHeight + "px");
        });
    }, 10);
    setTimeout(onResize,50);
}

function loadGoogleLogin() {
    gapi.load('auth2', function(){
        auth2 = gapi.auth2.init({
            client_id: '953285646027-r3rsel8atqu2g8nbn45ag1jc24lah7lg.apps.googleusercontent.com',
            cookiepolicy: 'single_host_origin'
        });
        attachSignin(document.getElementById('google-login-button'));
    });
}

function attachSignin(element) {
    console.log(element.id);
    auth2.attachClickHandler(element, {},onSignIn);
}

function initMap() {
    var image = "https://www.dropbox.com/s/pb99jcgjvrdzk0f/add_marker_icon.png?dl=1";
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 14,
        mapTypeControl: false,
        streetViewControl: false,
        draggableCursor : "url(" + image + ") 24 48, auto"
    });
    setLocation({
        lat: 51.9173624,
        lng: 4.4826242
    });
    map.addListener('click', getCustomLocation);
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

function keyDownNewDataInfo(e) {
    if (e.keyCode == 13) {
        e.preventDefault();
        saveDataInfo();
    }
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
        url: ROOT + "/includes/dataInfo.php",
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
    var item = $(this).parent(".entry-card");
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
        $("#location-map").height(0);
    } else {
        button.addClass("selected");
        $("#location-map").height(200);
    }
    resizeContent();
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        var icon = $("#current-location");
        navigator.geolocation.getCurrentPosition(function (position) {
            setLocation({
                lat: position.coords.latitude,
                lng: position.coords.longitude
            });
            map.setZoom(14);
            icon.removeClass("searching");
        });
        icon.addClass("searching selected");
    }
}

function getCustomLocation(e) {
    $("#current-location").removeClass("selected");
    setLocation(e.latLng);
}

function setLocation(pos) {
    map.panTo(pos);
    if(marker) marker.setMap(null);
    marker = new google.maps.Marker({
        position: pos,
        map: map
    });
    $("#lat").val(pos.lat).parent().delay(10).addClass("is-focused");
    $("#lng").val(pos.lng).parent().delay(10).addClass("is-focused");
}

function logout() {
    $.ajax({
        url: "includes/userCall.php",
        method: "post",
        data: {
            method: "logOut"
        },
        success: function(o) {
            location.reload();
        }
    })
}

function confirmDelete(e) {
    if (confirm("Weet je zeker dat je deze wilt verwijderen?")) $(e.currentTarget).parent().submit();

}

function onResize() {
    var content = $(".content-section");
    var max = $(window).height() - $(".mdl-layout__header").height() - 32 - parseInt(content.css("padding-top")) - parseInt(content.css("padding-bottom"));
    if (content.height() > max) content.addClass("static").removeClass("absolute");
    else content.addClass("absolute").removeClass("static");
    if ($("select").length > 0) $("select").material_select();
}

function isMobile () {
    return $(window).width() < 461;
}

function resizeContent() {
    var content = $("#add-item");
    content.css("max-height", content.height() + parseInt(content.css("padding-top")) + parseInt(content.css("padding-bottom")) + 300);
    onResize();
}
