<?php
require("includes/initialize.php");

if (isLoggedIn()) {
    $getCategories = "SELECT id, name FROM ".DB_PREFIX."category WHERE user_id = '".$_SESSION["userId"]."' ORDER BY name ASC";
    $categories = $conn->query($getCategories);

    $getDataTypes = "SELECT id, name FROM ".DB_PREFIX."dataType WHERE user_id = '".$_SESSION["userId"]."' ORDER BY name ASC";
    $dataTypes = $conn->query($getDataTypes);

    $getCompaniesQuery = "SELECT id, name FROM ".DB_PREFIX."company WHERE user_id = '".$_SESSION["userId"]."' ORDER BY name ASC";
    $companies = $conn->query($getCompaniesQuery);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MT-MEC Tracking Log</title>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0, user-scalable=yes">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.1.1/material.indigo-red.min.css">
    <link rel="stylesheet" href="css/lib/material.min.css">
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="css/lib/bootstrap-material-datetimepicker.css">
    <link rel="stylesheet" href="css/style.css">
    <meta name="google-signin-scope" content="profile email">
    <meta name="google-signin-client_id" content="953285646027-r3rsel8atqu2g8nbn45ag1jc24lah7lg.apps.googleusercontent.com">
    <script src="https://apis.google.com/js/platform.js" async defer></script>
</head>
<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <span class="mdl-layout-title">MT-MEC Tracking Log</span>
            <div class="mdl-layout-spacer"></div>
            <label class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" for="logout">
                <i id="logout" class="material-icons">exit_to_app</i>
            </label>
        </div>
    </header>
    <main class="mdl-layout__content">
        <div class="page-content">
            <?php if (isLoggedIn()) : ?>
                <section class="content-section mdl-card mdl-shadow--2dp centerab" id="add-item">
                    <form action="#">
                        <div class="section-header mdl-color--primary">
                            <h1 class="mdl-typography--title valign">Item toevoegen</h1>
                            <label id="quick-entry" class="mdl-switch mdl-js-switch mdl-js-ripple-effect valign" for="quick-entry-switch">
                                <input type="checkbox" id="quick-entry-switch" class="mdl-switch__input">
                                <span class="mdl-switch__label">Quick entry</span>
                            </label>
                        </div>
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                            <input class="mdl-textfield__input" type="text" id="title">
                            <label class="mdl-textfield__label" for="title">Titel</label>
                        </div>
                        <div class="field-add-button-container form-item">
                            <button type="button" id="category-select" class="mdl-button mdl-js-button mdl-js-ripple-effect">
                                <span>Categorie <i class="material-icons">keyboard_arrow_down</i></span>
                            </button>
                            <button type="button" data-data-info-type="category" data-data-info-text="Categorie" class="add-info-dialog-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect">
                                <i class="material-icons">add</i>
                            </button>
                            <ul id="category-list" class="mdl-menu mdl-menu--bottom-left mdl-js-menu mdl-js-ripple-effect" data-mdl-for="category-select">
                                <?php foreach($categories as $category) : ?>
                                    <li class="mdl-menu__item category-item">
                                        <input id="category-<?= $category["id"] ?>" value="<?= $category["id"] ?>" name="category" type="radio">
                                        <label for="category-<?= $category["id"] ?>"><?= $category["name"] ?></label>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                        <div class="field-add-button-container form-item">
                            Data types
                            <button type="button" data-data-info-type="dataType" data-data-info-text="Data type" class="add-info-dialog-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect">
                                <i class="material-icons">add</i>
                            </button>
                        </div>
                        <div id="data-type-list" class="checkbox-container">
                            <?php foreach($dataTypes as $dataType) : ?>
                                <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="datatype-<?= $dataType["id"] ?>">
                                    <input type="checkbox" id="datatype-<?= $dataType["id"] ?>" name="data-types" class="mdl-checkbox__input">
                                    <span class="mdl-checkbox__label"><?= $dataType["name"] ?></span>
                                </label>
                            <?php endforeach ?>
                        </div>
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                            <textarea class="mdl-textfield__input" type="text" rows= "3" id="description"></textarea>
                            <label class="mdl-textfield__label" for="description">Omschrijving</label>
                        </div>
                        Bedrijf
                        <button type="button" data-data-info-type="company" data-data-info-text="Bedrijf" class="add-info-dialog-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect">
                            <i class="material-icons">add</i>
                        </button>
                        <div id="company-list" class="checkbox-container">
                            <?php foreach($companies as $company) : ?>
                                <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect" for="company-<?= $company["id"] ?>">
                                    <input type="checkbox" id="company-<?= $company["id"] ?>" name="companies" class="mdl-checkbox__input">
                                    <span class="mdl-checkbox__label"><?= $company["name"] ?></span>
                                </label>
                            <?php endforeach ?>
                        </div>
                        <div id="date-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                            <input class="mdl-textfield__input" type="text" id="date">
                            <label class="mdl-textfield__label" for="date">Datum</label>
                        </div>
                        <div id="time-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item date-item">
                            <input class="mdl-textfield__input" type="text" id="time">
                            <label class="mdl-textfield__label" for="time">Tijd</label>
                        </div>
                        Lat (input field)
                        Long (input field)
                        Button current | Button map
                        Send
                    </form>
                </section>
            <?php else: ?>
                <section class="content-section mdl-card mdl-shadow--2dp" id="login-section">
                    <div class="section-header mdl-color--primary">
                        <h1 class="mdl-typography--title">Inloggen</h1>
                    </div>
                    <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div>
                </section>
            <?php endif ?>
        </div>
    </main>
</div>
<div class="mdl-dialog mdl-js-dialog" id="add-data-info-dialog">
    <div class="mdl-dialog__title">
        <h3></h3>
    </div>
    <div class="mdl-dialog__content">
        <form action="#">
            <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                <input class="mdl-textfield__input" type="text" id="add-data-info">
                <label class="mdl-textfield__label" for="add-data-info">Naam</label>
            </div>
            <input type="hidden" id="add-data-info-type">
        </form>
    </div>
    <div class="mdl-dialog__actions">
        <button type="button" id="save-add-info-button" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect">Toevoegen</button>
        <button type="button" id="cancel-add-info-button" class="mdl-button mdl-js-button mdl-js-ripple-effect">Annuleren</button>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="https://code.getmdl.io/1.1.1/material.min.js"></script>
<script src="js/lib/material.add.min.js"></script>
<script src="js/script.js"></script>
<script src="js/googleLogin.js"></script>
<script src="js/lib/moment.min.js"></script>
<script src="js/lib/moment.nl.js"></script>
<script src="js/lib/bootstrap-material-datetimepicker.js"></script>
<script>
    $(initApp);
</script>
</body>
</html>