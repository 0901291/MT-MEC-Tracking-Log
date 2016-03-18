<?php
require_once('includes/initialize.php');

if (isLoggedIn()) {
    $client = new GuzzleHttp\Client();
    $url = ROOT.'/api/V1/entry/';
    $key = $_SESSION['token'];
    $response = $client->request('GET', $url, ['query' => ['api_key' => $key, 'limit' => 5]]);
    $entries = (array)json_decode($response->getBody());
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
    <link rel="stylesheet" href="https://code.getmdl.io/1.1.1/material.indigo-red.min.css">
    <link rel="stylesheet" href="<?= ROOT ?>/css/lib/material.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" type="text/css">
    <link rel="stylesheet" href="<?= ROOT ?>/css/style.css">
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
                <a class="mdl-navigation__link active" href="<?= ROOT ?>/entries">Log</a>
                <a class="mdl-navigation__link" href="<?= ROOT ?>/export">Export</a>
                <a href="#" class="mdl-navigation__link logout" id="logout-desktop">
                    <label class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect" for="logout-desktop">
                        <i class="material-icons">exit_to_app</i>
                    </label>
                </a>
            </nav>
        </div>
        <div class="section-header header-section-header mdl-color--primary hidden">
            <h1 class="mdl-typography--title valign">Items</h1>
            <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect valign concept-switch header-switch" for="concept-switch-mobile">
                <input type="checkbox" id="concept-switch-mobile" class="mdl-switch__input">
                <span class="mdl-switch__label">Concepten</span>
            </label>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title">MT-MEC Tracking Log</span>
        <nav class="mdl-navigation">
            <a class="mdl-navigation__link" href="<?= ROOT ?>">Nieuw</a>
            <a class="mdl-navigation__link active" href="<?= ROOT ?>/entries">Log</a>
            <a class="mdl-navigation__link" href="<?= ROOT ?>/export">Export</a>
            <a class="mdl-navigation__link logout" href="#">Logout</a>
        </nav>
    </div>
    <main class="mdl-layout__content">
        <div class="page-content">
            <section class="content-section centerab static" id="items">
                <div class="section-header mdl-color--primary show-quick-entry mdl-card mdl-shadow--2dp">
                    <h1 class="mdl-typography--title valign">Items</h1>
                    <label class="mdl-switch mdl-js-switch mdl-js-ripple-effect valign concept-switch header-switch" for="concept-switch-desktop">
                        <input type="checkbox" id="concept-switch-desktop" class="mdl-switch__input">
                        <span class="mdl-switch__label">Concepten</span>
                    </label>
                </div>
                <?php foreach($entries['items'] as $key => $entry):
                    $concept = $entry->state == 1 ? true : false;
                    $empty = empty($entry->title) && count($entry->category) == 0 && empty($entry->description) && count($entry->dataTypes) == 0 && count($entry->companies) == 0 ? 'empty' : '';
                ?>
                    <div class="entry-card mdl-card mdl-shadow--2dp not-initialised collapsed ?> <?= $concept ? "concept-card" : "" ?> <?= $empty ?>" data-state="<?= $entry->state ?>">
                        <div class="entry-card-header">
                            <div class="valign">
                                <h2 class="ellipsis"><?php if ($concept) echo "<i class=\"material-icons valign concept-icon\">drafts</i>" ?><?= empty($entry->title) ? "<em>Geen titel</em>" : $entry->title ?></h2>
                                <span class="entry-date valign"><?= $entry->date ?></span>
                                <div class="form-container valign">
                                    <form action="<?= ROOT?>/entries/<?= $entry->id ?>/edit">
                                        <button type="submit" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect entry-edit entry-control">
                                            <input type="hidden" value="<?= $entry->id ?>">
                                            <i class="material-icons">mode_edit</i>
                                        </button>
                                    </form>
                                    <form action="<?= ROOT?>/includes/entryCall.php" method="post" class="delete-entry">
                                        <button type="button" class="mdl-button mdl-js-button mdl-button--icon mdl-js-ripple-effect entry-remove entry-control">
                                            <input type="hidden" name="method" value="delete">
                                            <input type="hidden" name="id" value="<?= $entry->id ?>">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="entry-card-content">
                            <?php if(!empty($entry->title)): ?>
                                <div class="entry-title">
                                    <h3 class="entry-section-heading">Titel</h3>
                                    <span><?= $entry->title ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if(count($entry->category) > 0): ?>
                                <div class="entry-category">
                                    <h3 class="entry-section-heading">Categorie</h3>
                                    <span><?= $entry->category->name ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($entry->description)): ?>
                                <div class="entry-description">
                                    <h3 class="entry-section-heading">Omschrijving</h3>
                                    <p><?= $entry->description ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if(count($entry->dataTypes) > 0): ?>
                                <div class="entry-datatypes">
                                    <h3 class="entry-section-heading">Data types</h3>
                                    <ul>
                                        <?php foreach($entry->dataTypes as $key => $dataType): ?>
                                            <li><?= $dataType ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if(count($entry->companies) > 0): ?>
                                <div class="entry-companies">
                                    <h3 class="entry-section-heading">Bedrijven</h3>
                                    <ul>
                                        <?php foreach($entry->companies as $key => $company): ?>
                                            <li><?= $company ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if(!empty($entry->location->lat && !empty($entry->location->lng))): ?>
                            <div class="entry-location">
                                <img data-src="https://maps.googleapis.com/maps/api/staticmap?center=<?= $entry->location->lat ?>,<?= $entry->location->lng ?>&zoom=14&size=460x130&maptype=roadmap&markers=color:red%7C<?= $entry->location->lat ?>,<?= $entry->location->lng ?>&key=AIzaSyC6VYBFTcvqfDookMW4Hl1J3TphwJxo6nA" alt="">
                                <div class="shadow"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="control-buttons">
                    <button type="button" data-items="5" id="load-more-button" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--raised mdl-button--primary">Laad 5 meer</button>
                    <button type="button" data-items="0" id="load-all-button" class="mdl-button mdl-js-button mdl-js-ripple-effect">Laad alles</button>
                </div>
            </section>
            <?php include("includes/footer.php"); ?>
        </div>
    </main>
</div>
<script><?= "var ROOT = '".ROOT."'; var API_KEY = '".$_SESSION['token']."'"; ?></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="https://code.getmdl.io/1.1.1/material.min.js"></script>
<script src="<?= ROOT ?>/js/lib/material.add.min.js"></script>
<script src="<?= ROOT ?>/js/script.js"></script>
<script>
    $(initApp);
</script>
</body>
</html>
