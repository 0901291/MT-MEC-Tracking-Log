<?php
require("includes/initialize.php");

if (isLoggedIn()) {
    $entries = Entry::getEntries($status = 0, $conn);
}
else {
    header("index.php");
    die();
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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
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
        <div class="section-header header-section-header mdl-color--primary hidden">
            <h1 class="mdl-typography--title valign">Items</h1>
            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect valign concept-switch header-switch" for="concept-switch-mobile">
                <input type="checkbox" id="concept-switch-mobile" class="mdl-switch__input">
                <span class="mdl-switch__label">Concepten</span>
            </label>
        </div>
    </header>
    <main class="mdl-layout__content">
        <div class="page-content">
            <section class="content-section centerab" id="items">
                <div class="section-header mdl-color--primary show-quick-entry mdl-card mdl-shadow--2dp">
                    <h1 class="mdl-typography--title valign">Items</h1>
                    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect valign concept-switch header-switch" for="concept-switch-desktop">
                        <input type="checkbox" id="concept-switch-desktop" class="mdl-switch__input">
                        <span class="mdl-switch__label">Concepten</span>
                    </label>
                </div>
                <?php foreach($entries as $entry) : ?>
                    <div class="entry-card mdl-card mdl-shadow--2dp">
                        <div class="entry-card-header">
                            <div class="valign">
                                <h2 class="ellipsis"><?= $entry["name"] ?></h2>
                                <span class="entry-date valign"><?= date("d-m-Y H:m", strtotime($entry["date"])) ?></span>
                                <div class="form-container valign">
                                    <form action="edit/<?= $entry["id"] ?>">
                                        <button type="submit" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect entry-edit">
                                            <input type="hidden" value="<?= $entry["id"] ?>">
                                            <i class="material-icons">mode_edit</i>
                                        </button>
                                    </form>
                                    <form action="../delete/<?= $entry["id"] ?>">
                                        <button type="submit" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect entry-remove">
                                            <input type="hidden" value="<?= $entry["id"] ?>">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="entry-card-content">

                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        </div>
    </main>
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