<?php
require ("includes/initialize.php");
if (isLoggedIn()) {
}
else {
    header('Location: '.ROOT);
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
    <link rel="stylesheet" href="<?= ROOT ?>/css/lib/materialize.min.css">
    <link rel="stylesheet" href="https://code.getmdl.io/1.1.1/material.indigo-red.min.css">
    <link rel="stylesheet" href="<?= ROOT ?>/css/lib/material.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="<?= ROOT ?>/css/style.css">
    <script src="https://apis.google.com/js/api:client.js"></script>
</head>
<body>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <div id="google-profile" class="valign">
                <img src="<?= $_SESSION["imgURL"] ?>" alt="">
                <div id="google-profile-inner" class="hidden mdl-color--primary mdl-shadow--2dp">
                    <span class="google-name"><?= $_SESSION["name"] ?></span>
                    <span class="google-email"><?= $_SESSION["email"] ?></span>
                </div>
            </div>
            <span class="mdl-layout-title">MT-MEC Tracking Log</span>
            <div class="mdl-layout-spacer"></div>
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <a class="mdl-navigation__link" href="<?= ROOT ?>">Nieuw</a>
                <a class="mdl-navigation__link" href="<?= ROOT ?>/entries">Log</a>
                <a class="mdl-navigation__link active" href="<?= ROOT ?>/export">Export</a>
                <a href="#" class="mdl-navigation__link logout" id="logout-desktop">
                    <label class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" for="logout-desktop">
                        <i class="material-icons">exit_to_app</i>
                    </label>
                </a>
            </nav>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">MT-MEC Tracking Log</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="<?= ROOT ?>">Nieuw</a>
            <a class="mdl-navigation__link" href="<?= ROOT ?>/entries">Log</a>
            <a class="mdl-navigation__link active" href="<?= ROOT ?>/export">Export</a>
            <a class="mdl-navigation__link logout" href="#">Logout</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <div class="page-content">
            <section class="content-section mdl-card mdl-shadow--2dp centerab" id="export">
                <div class="section-header mdl-color--primary show-quick-entry">
                    <h1 class="mdl-typography--title valign">Export</h1>
                </div>
                <div>
                    <p>
                        De Tracklog API is een RESTful webservice. Je stuurt met elk request jouw unieke API key mee (<?= $_SESSION['token'] ?>). Ook geef je aan welke versie van de API je wilt gebruiken (meest recente versie: 1). Bij collections is het ook mogelijk om het resultaat te beperken tot een bepaald aantal (?limit=) en een offset mee te geven (?offset=).
                    </p>
                    <h2 class="mdl-typography--headline">Excel</h2>
                    <ul>
                        <li>
                            GET entries: <a target="_blank" href="<?=ROOT?>/api/v1/entry.xls?api_key=<?=$_SESSION['token']?>"><?=ROOT?>/api/v1/entry.xls?api_key=<?=$_SESSION['token']?></a>
                        </li>
                    </ul>
                    <h2 class="mdl-typography--headline">Entry</h2>
                    <ul>
                        <li>
                            GET entries: <a target="_blank" href="<?=ROOT?>/api/v1/entry/?api_key=<?=$_SESSION['token']?>"><?=ROOT?>/api/v1/entry?api_key=<?=$_SESSION['token']?></a>
                        </li>
                        <li>
                           POST entry:  <a target="_blank" href="<?=ROOT?>/api/v1/entry/?api_key=<?=$_SESSION['token']?>"><?=ROOT?>/api/v1/entry?api_key=<?=$_SESSION['token']?></a>
                            <p>
                                Bedrijven, data types en categorieën kunnen textueel worden ingevoerd. Als deze bij het versturen nog niet bestaan, worden ze aangemaakt. Bestonden ze al, worden de bestaande items gebruikt. Let hierbij wel op hoofdletters.
                            </p>
                            <p>Voorbeeld POST request:</p>
<pre>{
    "title": "Inchecken trein",
    "date": "<?= date("Y-m-d H:i:s") ?>",
    "description": "",
    "imgURL": "",
    "lat": "52.063443",
    "lng": "5.1165763",
    "companies": [
        "NS",
        "Albert Heijn"
    ],
    "dataTypes": [
        "Visueel",
        "Locatie"
    ],
    "category": "Openbaar vervoer"
}</pre>
                        </li>
                        <li>
                            GET entry:  <a target="_blank" href="<?=ROOT?>/api/v1/entry/0?api_key=<?=$_SESSION['token']?>"><?=ROOT?>/api/v1/entry/<strong>{id}</strong>?api_key=<?=$_SESSION['token']?></a>
                        </li>
                        <li>
                            PUT entry:  <a target="_blank" href="<?=ROOT?>/api/v1/entry/0?api_key=<?=$_SESSION['token']?>"><?=ROOT?>/api/v1/entry/<strong>{id}</strong>?api_key=<?=$_SESSION['token']?></a>
                        </li>
                        <li>
                            DELETE entry:  <a target="_blank" href="<?=ROOT?>/api/v1/entry/0?api_key=<?=$_SESSION['token']?>"><?=ROOT?>/api/v1/entry/<strong>{id}</strong>?api_key=<?=$_SESSION['token']?></a>
                        </li>
                    </ul>
                    <em>De API is een work in progress, er zullen steeds nieuwe functies bijkomen.</em>
                </div>
            </section>
            <?php include("includes/footer.php"); ?>
        </div>
    </main>
</div>
<script><?= "var ROOT = '".ROOT."';"; ?></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="https://code.getmdl.io/1.1.1/material.min.js"></script>
<script src="<?= ROOT ?>/js/lib/materialize.min.js"></script>
<script src="<?= ROOT ?>/js/lib/material.add.min.js"></script>
<script src="<?= ROOT ?>/js/script.js"></script>
<script>
    $(initApp);
</script>
</body>
</html>