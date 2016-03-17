var map, marker;

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
    $("#location-fields").on("keydown", "input[type=text]", function () {$("#current-location").removeClass("selected");});
    $(".entry-remove").on("click", confirmDelete);
    $(".logout").on("click", logout);
    $("#submit-entry-button").on("click", checkLocationBeforeSend);
    $("#items").on("click", ".entry-card-header", toggleItem);
    $(".field-add-button-container").on("contentChange", function () {$(this).find(".dropdown-content").css("top", 0);});
    $("body").on(isMobile() ? "touchstart" : "click", closeSelectBox);
    $(".control-buttons button").on("click", loadMoreItems);
    initMap();
    if ($("select").length > 0) $("select").material_select();
    initializeEntryCards();
    $($(".entry-card")[0]).find('.entry-card-header').trigger('click');
    $(document).ready(resizeContent);
    setTimeout(resizeContent, 100);
    $(window).on("resize", resizeContent);
}

function loadGoogleLogin() {
    gapi.load('auth2', function(){
        auth2 = gapi.auth2.init({
            client_id: '953285646027-r3rsel8atqu2g8nbn45ag1jc24lah7lg.apps.googleusercontent.com',
            cookiepolicy: 'single_host_origin'
        });
        auth2.attachClickHandler(document.getElementById('google-login-button'), {}, onSignIn);
    });
}

function onSignIn(googleUser) {
    var profile = googleUser.getBasicProfile();
    $.ajax({
        data: {
            name: profile.getName(),
            id: profile.getId(),
            email: profile.getEmail(),
            img: profile.getImageUrl(),
            method: "logIn"
        },
        url: "includes/userCall.php",
        method: "POST",
        success: function () {
            location.reload();
        }
    });
}

function initMap() {
    if ($("#map").length == 0) return;
    var image = "https://www.dropbox.com/s/pb99jcgjvrdzk0f/add_marker_icon.png?dl=1";
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 14,
        mapTypeControl: false,
        streetViewControl: false,
        draggableCursor : "url(" + image + ") 24 48, auto"
    });
    map.addListener('click', getCustomLocation);
    var input = document.getElementById('search-location');
    var searchBox = new google.maps.places.SearchBox(input);
    searchBox.addListener('places_changed', function() {
        var places = searchBox.getPlaces();
        var location = places[0].geometry.location;
        if (places.length > 0) {
            $("#current-location").removeClass("selected");
            setLocation({
                lat: location.lat(),
                lng: location.lng()
            });
        }
    });
    $("#search-control").on("click",function () {
        google.maps.event.trigger(input, 'focus');
        google.maps.event.trigger(input, 'keydown', {
            keyCode: 13
        });
    });
    if ($("body").hasClass("edit-mode")) {
        setLocation({
            lat: parseFloat($("#lat").val()),
            lng: parseFloat($("#lng").val())
        });
    } else {
        setLocation({
            lat: 51.9173624,
            lng: 4.4826242
        });
        getCurrentLocation();
    }
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
    var item = null;
    var itemList = $("#" + type + "-list");
    itemList.find("option").each(function () {
        if ($(this).text().toLowerCase() == name.toLowerCase()) item = $(this);
    });
    if (!item) {
        addDataInfoToList(type, id, name);
    } else {
        if (itemList.attr("multiple") == "multiple") {
            var arr = itemList.val();
            arr.push(item.val());
            itemList.val(arr);
        } else {
            itemList.val(item.val());
        }
        itemList.material_select();
    }
    closeAddInfoDialog();
}

function addDataInfoToList(type, id, name) {
    var item = "<option selected value=\"" + id + "\">" + name + "</option>";
    $("#" + type + "-list").append(item);
    $("select").material_select();
    var content = $("#add-item");
    content.css("max-height", "initial").css("max-height", content.height() + parseInt(content.css("padding-top")) + parseInt(content.css("padding-bottom")));
}

function closeAddInfoDialog() {
    $('#add-data-info-dialog')[0].MaterialDialog.close();
    $("#add-data-info").val("");
}

function closeSelectBox(e) {
    e.stopPropagation();
    var el = $(e.target);
    if ($(".dropdown-content:visible").length > 0 && !el.hasClass("dropdown-content") && el.parents(".dropdown-content").length == 0) {
        console.log("click!");
        var dropdown = $("#" + $(".dropdown-content:visible").attr("id"));
        $("select").material_select();
    }
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
            setTimeout(onResize, 100);
        }, 310);
    } else {
        $(".quick-entry").removeClass("is-checked");
        toggle1.prop("checked", false);
        toggle2.prop("checked", false);
        content.removeClass("hide-items");
        setTimeout(function () {
            content.removeClass("quick-entry-mode");
            setTimeout(function () {
                setTimeout(onResize, 100);
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

function initializeEntryCards() {
    $.each($(".not-initialised"), initEntryCard);
}

function initEntryCard(k, el) {
    el = $(el);
    el.attr("data-max-height", el[0].scrollHeight);
    var maxHeight = 56;
    el.css("max-height", maxHeight + "px");
    el.removeClass("not-initialised");
    resizeContent();
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
                resizeContent();
            }, 10);
        }, 210);
        item.removeClass("collapsed");
        setTimeout(function () {
            item.addClass("show").css("max-height", item.attr("data-max-height") + "px");
            setTimeout(function () {
                var img = $(item.find(".entry-location img"));
                img.attr("src", img.attr("data-src"));
                resizeContent();
            }, 210);
        }, 10);
    } else {
        item.removeClass("show").css("max-height", maxHeightColl + "px");
        setTimeout(function () {
            item.addClass("collapsed");
            setTimeout(function () {
                resizeContent();
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
    setTimeout(resizeContent, 300);
}

function searchForLocation() {
    $.ajax({
        url: 'https://maps.googleapis.com/maps/api/geocode/json?address=' + $('#search-location').val().replace(' ', '+'),
        dataType: 'json',
        success: function (output) {
            console.log(output);
        }
    })
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
    if (marker) marker.setMap(null);
    marker = new google.maps.Marker({
        position: pos,
        map: map
    });
    $("#lat").val(pos.lat).parent().delay(10).addClass("is-focused");
    $("#lng").val(pos.lng).parent().delay(10).addClass("is-focused");
}

function loadMoreItems() {
    var itemsToLoad = $(this).data("items");
    var start = $('.entry-card').length;
    $.ajax({
        url: 'includes/entryCall.php',
        method: 'post',
        data: {
            method: 'getItems',
            limit: itemsToLoad,
            offset: start
        },
        dataType: 'json',
        success: function (items) {
            if (items.length > 0) printMoreItems(items);
            if (items.length == 0 || itemsToLoad == 0 || items.length < itemsToLoad) {
                $(".control-buttons button").attr("disabled", "");
            }
        }
    })
}

function printMoreItems(items) {
    var itemsHTML = "";
    $.each(items, function (k, v) {
        var item = "<div class=\"entry-card mdl-card mdl-shadow--2dp collapsed not-initialised " + (v.state == 1 ? "concept-card" : "" ) + " data-state=\"" + v.state + "\">" +
                        "<div class=\"entry-card-header\">" +
                            "<div class=\"valign\">" +
                                "<h2 class=\"ellipsis\">" + (v.state == 1 ? "<i class=\"material-icons valign concept-icon\">drafts</i>" : "") + (v.title.length == 0 ? "<em>Geen titel</em>" : v.title) + "</h2>" +
                                "<span class=\"entry-date valign\">" + v.date + "</span>" +
                                "<div class=\"form-container valign\">" +
                                    "<form action=\"" + ROOT + "/entries/" + v.id + "/edit\">" +
                                        "<button type=\"submit\" class=\"mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect entry-edit entry-control\">" +
                                            "<input type=\"hidden\" value=\"" + v.id + "\">" +
                                            "<i class=\"material-icons\">mode_edit</i>" +
                                        "</button>" +
                                    "</form>" +
                                    "<form action=\"" + ROOT + "/entries/" + v.id + "/delete\">" +
                                        "<button type=\"button\" class=\"mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect entry-remove entry-control\">" +
                                            "<input type=\"hidden\" value=\"" + v.id + "\">" +
                                            "<i class=\"material-icons\">delete</i>" +
                                        "</button>" +
                                    "</form>" +
                                "</div>" +
                            "</div>" +
                        "</div>" +
                    "<div class=\"entry-card-content\">";
        if (v.title.length > 0) {
            item += "<div class=\"entry-title\">" +
                        "<h3 class=\"entry-section-heading\">Titel</h3>" +
                        "<span>" + v.title + "</span>" +
                    "</div>";
        }
        if (v.category != null) {
            item += "<div class=\"entry-category\">" +
                        "<h3 class=\"entry-section-heading\">Categorie</h3>" +
                        "<span>" + v.category.name + "</span>" +
                    "</div>";
        }
        if (v.description.length > 0) {
            item += "<div class=\"entry-description\">" +
                        "<h3 class=\"entry-section-heading\">Omschrijving</h3>" +
                        "<p>" + v.description + "</p>" +
                    "</div>";
        }
        if (v.dataTypes.length > 0) {
            item += "<div class=\"entry-datatypes\">" +
                        "<h3 class=\"entry-section-heading\">Data types</h3>" +
                        "<ul>";
            $.each(v.dataTypes, function () {
                item += "<li>" + this + "</li>";
            });
            item += "</ul>" +
                "</div>";
        }
        if (v.companies.length > 0) {
            item += "<div class=\"entry-companies\">" +
                "<h3 class=\"entry-section-heading\">Bedrijven</h3>" +
                "<ul>";
            $.each(v.companies, function () {
                item += "<li>" + this + "</li>";
            });
            item += "</ul>" +
                "</div>";
        }
        item += "</div>";
        if (v.location.lat.length > 0 && v.location.lng.length > 0) {
            item += "<div class=\"entry-location\">" +
                        "<img src=\"https://maps.googleapis.com/maps/api/staticmap?center=" + v.location.lat + "," + v.location.lng + "&zoom=14&size=460x130&maptype=roadmap&markers=color:red%7C" + v.location.lat + "," + v.location.lng + "&key=AIzaSyC6VYBFTcvqfDookMW4Hl1J3TphwJxo6nA\" alt=\"\">" +
                        "<div class=\"shadow\"></div>" +
                    "</div>";
        }
        item += "</div>";
        itemsHTML += item;
    });
    $(".control-buttons").before(itemsHTML);
    initializeEntryCards();
}

function logout() {
    $.ajax({
        url: "includes/userCall.php",
        method: "post",
        data: {
            method: "logOut"
        },
        success: function() {
            location.reload();
        }
    })
}

function confirmDelete(e) {
    if (confirm("Weet je zeker dat je deze wilt verwijderen?")) $(e.currentTarget).parent().submit();
}

function checkLocationBeforeSend() {
    if (!$("#current-location").hasClass("searching") || confirm("Je locatie is nog niet bepaald, toch opslaan?")) $("#entry-form").submit();
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
