<?php
require ("includes/initialize.php");
require ('includes/objects/User.php');
require ('api/V1/objects/DataInfo.php');

use PHP_Crypt\PHP_Crypt as PHP_Crypt;

$categories; $dataTypes; $companies;
$conn = $db->getConnection();
$edit = false;

if (isLoggedIn()) {
    $crypt = new PHP_Crypt($_SESSION['key']);
    $getCategories = "SELECT id, name FROM ".DB_PREFIX."category WHERE user_id = '".$_SESSION["userId"]."' ORDER BY name ASC";
    $_categories = $conn->query($getCategories);
    $categories = [];
    foreach ($_categories as $_category) {
        $categories[] = [
            "id" => $_category["id"],
            "name" => trim($crypt->decrypt(hex2bin($_category["name"])))
        ];
    }

    $getDataTypes = "SELECT id, name FROM ".DB_PREFIX."datatype WHERE user_id = '".$_SESSION["userId"]."' ORDER BY name ASC";
    $_dataTypes = $conn->query($getDataTypes);
    $dataTypes = [];
    foreach ($_dataTypes as $_dataType) {
        $dataTypes[] = [
            "id" => $_dataType["id"],
            "name" => trim($crypt->decrypt(hex2bin($_dataType["name"])))
        ];
    }

    $getCompaniesQuery = "SELECT id, name FROM ".DB_PREFIX."company WHERE user_id = '".$_SESSION["userId"]."' ORDER BY name ASC";
    $_companies = $conn->query($getCompaniesQuery);

    $companies = [];
    foreach ($_companies as $_company) {
        $companies[] = [
            "id" => $_company["id"],
            "name" => trim($crypt->decrypt(hex2bin($_company["name"])))
        ];
    }

    $title = $description = $lat = $lng = $id = "";
    $date = date("Y-m-d");
    $time = date("H:i");
    $categoryId = -1;
    $dataTypeIds = $companyIds = [];

    if (isset($_GET["method"]) && isset($_GET["data_id"])) {
        if ($_GET["method"] == "edit") {
            $edit = true;
            $client = new GuzzleHttp\Client();
            $url = ROOT.'/api/V1/entry/'.$_GET['data_id'];
            $key = $_SESSION['token'];
            $response = $client->request('GET', $url, ["query" => ["api_key" => $key]]);
            $entry = (array)json_decode($response->getBody());
            $id = $entry['item']->id;
            $title = $entry['item']->title;
            $description = $entry['item']->description;
            $lat = $entry['item']->location->lat;
            $lng = $entry['item']->location->lng;
            $date = date("Y-m-d", strtotime($entry['item']->date));
            $time = date("H:i", strtotime($entry['item']->date));
            $categoryId = isset($entry['item']->category->id) ? $entry['item']->category->id : null;
            foreach($entry['item']->dataTypes as $dataType) $dataTypeIds[] = $dataType->id;
            foreach($entry['item']->companies as $company) $companyIds[] = $company->id;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MT-MEC Tracking Log</title>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0, user-scalable=yes">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="<?= ROOT ?>/css/lib/materialize.min.css">
    <link rel="stylesheet" href="https://code.getmdl.io/1.1.1/material.indigo-red.min.css">
    <link rel="stylesheet" href="<?= ROOT ?>/css/lib/material.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="<?= ROOT ?>/css/style.css">
    <script src="https://apis.google.com/js/api:client.js"></script>
</head>
<body class="<?= $edit ? "edit-mode" : ""?>">
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <?php if (isLoggedIn()) : ?>
                <div id="google-profile" class="valign">
                    <img src="<?= $_SESSION["imgURL"] ?>" alt="">
                    <div id="google-profile-inner" class="hidden mdl-color--primary mdl-shadow--2dp">
                        <span class="google-name"><?= $_SESSION["name"] ?></span>
                        <span class="google-email"><?= $_SESSION["email"] ?></span>
                    </div>
                </div>
            <?php endif; ?>
            <span class="mdl-layout-title">MT-MEC Tracking Log</span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link active" href="<?= ROOT ?>">Nieuw</a>
                <?php if (isLoggedIn()) : ?>
                    <a class="mdl-navigation__link" href="<?= ROOT ?>/entries">Log</a>
                    <a class="mdl-navigation__link" href="<?= ROOT ?>/export">Export</a>
                    <a href="#" class="mdl-navigation__link logout" id="logout-desktop">
                        <label class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" for="logout-desktop">
                            <i class="material-icons">exit_to_app</i>
                        </label>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
        <?php if (isLoggedIn()) : ?>
            <div class="section-header header-section-header mdl-color--primary show-quick-entry hidden">
                <h1 class="mdl-typography--title valign">Item toevoegen</h1>
                <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect valign quick-entry header-switch" for="quick-entry-switch-mobile">
                    <input type="checkbox" id="quick-entry-switch-mobile" class="mdl-switch__input">
                    <span class="mdl-switch__label">Quick entry</span>
                </label>
            </div>
        <?php else: ?>
            <div class="section-header header-section-header mdl-color--primary show-quick-entry hidden">
                <h1 class="mdl-typography--title valign">Inloggen</h1>
            </div>
        <?php endif; ?>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">MT-MEC Tracking Log</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link active" href="<?= ROOT ?>">Nieuw</a>
            <?php if (isLoggedIn()) : ?>
                <a class="mdl-navigation__link" href="<?= ROOT ?>/entries">Log</a>
                <a class="mdl-navigation__link" href="<?= ROOT ?>/export">Export</a>
                <a class="mdl-navigation__link logout" href="#">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <div class="page-content">
            <?php if (isLoggedIn()) : ?>
                <section class="content-section mdl-card mdl-shadow--2dp centerab" id="add-item">
                    <div class="section-header mdl-color--primary show-quick-entry">
                        <h1 class="mdl-typography--title valign">Item toevoegen</h1>
                        <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect valign quick-entry header-switch" for="quick-entry-switch-desktop">
                            <input type="checkbox" id="quick-entry-switch-desktop" class="mdl-switch__input">
                            <span class="mdl-switch__label">Quick entry</span>
                        </label>
                    </div>
                    <form id="entry-form" action="<?= ROOT ?>/includes/entryCall.php" method="post">
                        <div id="title-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item show-quick-entry">
                            <input class="mdl-textfield__input" type="text" id="title" name="title" value="<?= $title ?>">
                            <label class="mdl-textfield__label" for="title">Titel</label>
                        </div>
                        <div class="field-add-button-container form-item">
                            <label class="add-entry-section-heading" for="category">Categorie</label>
                            <div class="input-field row">
                                <select name="category" id="category-list" class="col s11">
                                    <option value="" disabled selected>Category</option>
                                    <?php foreach($categories as $key => $category) : ?>
                                        <option <?= $categoryId == $category['id'] ? "selected" : "" ?> value="<?= $category["name"] ?>"><?= $category["name"] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <div class="col s1">
                                    <button type="button" data-data-info-type="category" data-data-info-text="Categorie" class="add-info-dialog-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                            <textarea class="mdl-textfield__input" type="text" rows= "3" id="description" name="description"><?= $description ?></textarea>
                            <label class="mdl-textfield__label" for="description">Omschrijving</label>
                        </div>
                        <div class="field-add-button-container form-item">
                            <label class="add-entry-section-heading" for="datatypes">Data types</label>
                            <div class="input-field row">
                                <select multiple name="dataTypes[]" id="datatype-list" class="col s11">
                                    <option value="" disabled selected>Data types</option>
                                    <?php foreach($dataTypes as $key => $dataType) : ?>
                                        <option <?= in_array($dataType['id'], $dataTypeIds) ? "selected" : "" ?> value="<?= $dataType["name"] ?>"><?= $dataType["name"] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <div class="col s1">
                                    <button type="button" data-data-info-type="datatype" data-data-info-text="Data type" class="add-info-dialog-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="field-add-button-container form-item">
                            <label class="add-entry-section-heading" for="companies">Bedrijven</label>
                            <div class="input-field row">
                                <select multiple name="companies[]" id="company-list" class="col s11">
                                    <option value="" disabled selected>Bedrijven</option>
                                    <?php foreach($companies as $key => $company) : ?>
                                        <option <?= in_array($company['id'], $companyIds) ? "selected" : "" ?> value="<?= $company["name"] ?>"><?= $company["name"] ?></option>
                                    <?php endforeach ?>
                                </select>
                                <div class="col s1">
                                    <button type="button" data-data-info-type="company" data-data-info-text="Bedrijf" class="add-info-dialog-button mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="date-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item date-picker">
                            <input class="mdl-textfield__input" type="date" id="date" name="date" value="<?= $date ?>">
                            <label class="mdl-textfield__label" for="date">Datum</label>
                        </div>
                        <div id="time-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item date-item date-picker">
                            <input class="mdl-textfield__input" type="text" id="time" name="time" value="<?= $time ?>">
                            <label class="mdl-textfield__label" for="time">Tijd</label>
                        </div>
                        <div id="search-location-wrapper">
                            <div id="search-location-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                                <input class="mdl-textfield__input" placeholder="" type="text" id="search-location" name="search-location">
                                <label class="mdl-textfield__label" for="date">Search location</label>
                            </div>
                            <div id="search-control">
                                <button id="search-location-button" type="button" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect valign">
                                    <i class="material-icons">search</i>
                                </button>
                            </div>
                        </div>
                        <div id="location">
                            <div id="location-fields">
                                <div id="lat-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                                    <input class="mdl-textfield__input" type="text" id="lat" name="lat" value="<?= $lat ?>">
                                    <label class="mdl-textfield__label" for="date">Latitude</label>
                                </div>
                                <div id="lng-field" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label form-item">
                                    <input class="mdl-textfield__input" type="text" id="lng" name="lng" value="<?= $lng ?>">
                                    <label class="mdl-textfield__label" for="date">Longitude</label>
                                </div>
                            </div>
                            <div id="location-controls" class="valign">
                                <button id="current-location" type="button" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect location-control">
                                    <i class="material-icons">my_location</i>
                                </button>
                                <button id="toggle-map-button" type="button" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect location-control">
                                    <i class="material-icons">map</i>
                                </button>
                            </div>
                        </div>
                        <div id="location-map">
                            <div id="map"></div>
                        </div>
                        <div id="submit-entry" class="show-quick-entry">
                            <input type="hidden" name="method" value="<?= $edit ? "edit" : "insert" ?>">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <button type="button" id="submit-entry-button" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--primary"><?= $edit ? "Opslaan" : "Toevoegen" ?></button>
                        </div>
                    </form>
                </section>
            <?php else: ?>
                <section class="content-section mdl-card mdl-shadow--2dp centerab" id="login">
                    <div class="section-header mdl-color--primary show-quick-entry">
                        <h1 class="mdl-typography--title valign">Inloggen</h1>
                    </div>
                    <p>Login met je Google account en voeg gelijk nieuwe data toe.</p>
                    <button id="google-login-button" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--primary mdl-button--raised">Inloggen met Google</button>
                </section>
            <?php endif; ?>
            <?php include("includes/footer.php"); ?>
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
<script><?= "var ROOT = '".ROOT."';"; ?></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="https://code.getmdl.io/1.1.1/material.min.js"></script>
<script src="<?= ROOT ?>/js/lib/materialize.min.js"></script>
<script src="<?= ROOT ?>/js/lib/material.add.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC6VYBFTcvqfDookMW4Hl1J3TphwJxo6nA&libraries=places"></script>
<script src="<?= ROOT ?>/js/script.js"></script>
<script>
    $(initApp);
</script>
</body>
</html>